<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameRound;
use App\Models\PaymentTransaction;
use App\Models\PlayerBonus;
use App\Models\Promotion;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletLedgerEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WalletLedgerService
{
    public function __construct(
        protected AuditLoggerService $auditLogger
    ) {}

    /**
     * Credit a confirmed USD deposit into the player's cash balance (and bonus if attached).
     */
    public function creditDeposit(PaymentTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $wallet = Wallet::where('user_id', $transaction->user_id)->lockForUpdate()->firstOrFail();
            $user = $wallet->user;

            $cashBefore = (float) $wallet->cash_balance_usd;
            $bonusBefore = (float) $wallet->bonus_balance_usd;
            $lockedBefore = (float) $wallet->locked_withdrawal_usd;

            $depositAmount = (float) $transaction->net_amount_usd;
            $cashAfter = $cashBefore + $depositAmount;

            $wallet->cash_balance_usd = $cashAfter;
            $wallet->lifetime_deposited_usd = (float) $wallet->lifetime_deposited_usd + $depositAmount;
            $wallet->save();

            // Insert cash deposit ledger entry
            WalletLedgerEntry::create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'entry_reference' => 'LED-DEP-' . strtoupper(Str::random(10)),
                'idempotency_key' => 'idemp-dep-' . $transaction->reference,
                'entry_type' => 'deposit_credit',
                'balance_bucket' => 'cash',
                'amount_usd' => $depositAmount,
                'cash_balance_before_usd' => $cashBefore,
                'cash_balance_after_usd' => $cashAfter,
                'bonus_balance_before_usd' => $bonusBefore,
                'bonus_balance_after_usd' => $bonusBefore,
                'locked_balance_before_usd' => $lockedBefore,
                'locked_balance_after_usd' => $lockedBefore,
                'related_type' => PaymentTransaction::class,
                'related_id' => $transaction->id,
                'description_en' => "Confirmed USD Deposit via {$transaction->paymentMethod->name_en} ({$transaction->reference})",
                'description_id' => "Deposit USD Terkonfirmasi melalui {$transaction->paymentMethod->name_id} ({$transaction->reference})",
                'actor_user_id' => $user->id,
                'created_at' => now(),
            ]);

            // Handle attached promotional bonus
            if ($transaction->promotion_id) {
                $promotion = Promotion::find($transaction->promotion_id);
                if ($promotion && $promotion->isActive()) {
                    app(BonusRolloverService::class)->attachPromotion($user, $promotion, $depositAmount, $transaction);
                }
            }
        });
    }

    /**
     * Debit a game bet using real-money-first rule.
     */
    public function debitGameBet(Wallet $wallet, float $betAmount, GameRound $round): void
    {
        $cashBefore = (float) $wallet->cash_balance_usd;
        $bonusBefore = (float) $wallet->bonus_balance_usd;
        $lockedBefore = (float) $wallet->locked_withdrawal_usd;

        if (($cashBefore + $bonusBefore) < $betAmount) {
            throw new InvalidArgumentException('Insufficient funds in wallet for this bet.');
        }

        // Real-Money-First: deduct cash first, then bonus
        if ($cashBefore >= $betAmount) {
            $cashDebit = $betAmount;
            $bonusDebit = 0.00;
        } else {
            $cashDebit = $cashBefore;
            $bonusDebit = $betAmount - $cashBefore;
        }

        $cashAfter = $cashBefore - $cashDebit;
        $bonusAfter = $bonusBefore - $bonusDebit;

        $wallet->cash_balance_usd = $cashAfter;
        $wallet->bonus_balance_usd = $bonusAfter;
        $wallet->lifetime_wagered_usd = (float) $wallet->lifetime_wagered_usd + $betAmount;
        $wallet->save();

        // Update round components
        $round->cash_bet_component_usd = $cashDebit;
        $round->bonus_bet_component_usd = $bonusDebit;
        $round->save();

        if ($cashDebit > 0) {
            WalletLedgerEntry::create([
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'entry_reference' => 'LED-BET-CASH-' . strtoupper(Str::random(10)),
                'idempotency_key' => 'idemp-bet-c-' . $round->round_code,
                'entry_type' => 'game_bet_debit',
                'balance_bucket' => 'cash',
                'amount_usd' => -$cashDebit,
                'cash_balance_before_usd' => $cashBefore,
                'cash_balance_after_usd' => $cashAfter,
                'bonus_balance_before_usd' => $bonusBefore,
                'bonus_balance_after_usd' => $bonusBefore,
                'locked_balance_before_usd' => $lockedBefore,
                'locked_balance_after_usd' => $lockedBefore,
                'related_type' => GameRound::class,
                'related_id' => $round->id,
                'description_en' => "Wager on {$round->game->title} (Round #{$round->round_code})",
                'description_id' => "Taruhan pada {$round->game->title} (Ronde #{$round->round_code})",
                'actor_user_id' => $wallet->user_id,
                'created_at' => now(),
            ]);
        }

        if ($bonusDebit > 0) {
            WalletLedgerEntry::create([
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'entry_reference' => 'LED-BET-BONUS-' . strtoupper(Str::random(10)),
                'idempotency_key' => 'idemp-bet-b-' . $round->round_code,
                'entry_type' => 'game_bet_debit',
                'balance_bucket' => 'bonus',
                'amount_usd' => -$bonusDebit,
                'cash_balance_before_usd' => $cashAfter,
                'cash_balance_after_usd' => $cashAfter,
                'bonus_balance_before_usd' => $bonusBefore,
                'bonus_balance_after_usd' => $bonusAfter,
                'locked_balance_before_usd' => $lockedBefore,
                'locked_balance_after_usd' => $lockedBefore,
                'related_type' => GameRound::class,
                'related_id' => $round->id,
                'description_en' => "Bonus Wager on {$round->game->title} (Round #{$round->round_code})",
                'description_id' => "Taruhan Bonus pada {$round->game->title} (Ronde #{$round->round_code})",
                'actor_user_id' => $wallet->user_id,
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Credit game winnings.
     */
    public function creditGameWin(Wallet $wallet, float $payoutAmount, GameRound $round): void
    {
        if ($payoutAmount <= 0) {
            return;
        }

        $cashBefore = (float) $wallet->cash_balance_usd;
        $bonusBefore = (float) $wallet->bonus_balance_usd;
        $lockedBefore = (float) $wallet->locked_withdrawal_usd;

        // If the round bet used bonus and player has an active bonus with incomplete rollover, credit to bonus
        $activeBonus = $wallet->user->activeBonus;
        $targetBucket = 'cash';

        if ($round->bonus_bet_component_usd > 0 && $activeBonus && $activeBonus->status === 'active') {
            $targetBucket = 'bonus';
            $bonusAfter = $bonusBefore + $payoutAmount;
            $cashAfter = $cashBefore;
            $wallet->bonus_balance_usd = $bonusAfter;
        } else {
            $cashAfter = $cashBefore + $payoutAmount;
            $bonusAfter = $bonusBefore;
            $wallet->cash_balance_usd = $cashAfter;
        }

        $wallet->lifetime_won_usd = (float) $wallet->lifetime_won_usd + $payoutAmount;
        $wallet->save();

        WalletLedgerEntry::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'entry_reference' => 'LED-WIN-' . strtoupper(Str::random(10)),
            'idempotency_key' => 'idemp-win-' . $round->round_code,
            'entry_type' => 'game_win_credit',
            'balance_bucket' => $targetBucket,
            'amount_usd' => $payoutAmount,
            'cash_balance_before_usd' => $cashBefore,
            'cash_balance_after_usd' => $cashAfter,
            'bonus_balance_before_usd' => $bonusBefore,
            'bonus_balance_after_usd' => $bonusAfter,
            'locked_balance_before_usd' => $lockedBefore,
            'locked_balance_after_usd' => $lockedBefore,
            'related_type' => GameRound::class,
            'related_id' => $round->id,
            'description_en' => "Payout win from {$round->game->title} ({$round->multiplier}x Round #{$round->round_code})",
            'description_id' => "Kemenangan dari {$round->game->title} ({$round->multiplier}x Ronde #{$round->round_code})",
            'actor_user_id' => $wallet->user_id,
            'created_at' => now(),
        ]);
    }

    /**
     * Rollback a game bet atomically.
     */
    public function rollbackGameBet(Wallet $wallet, GameRound $round): void
    {
        $cashBefore = (float) $wallet->cash_balance_usd;
        $bonusBefore = (float) $wallet->bonus_balance_usd;
        $lockedBefore = (float) $wallet->locked_withdrawal_usd;

        $refundCash = (float) $round->cash_bet_component_usd;
        $refundBonus = (float) $round->bonus_bet_component_usd;

        $cashAfter = $cashBefore + $refundCash;
        $bonusAfter = $bonusBefore + $refundBonus;

        $wallet->cash_balance_usd = $cashAfter;
        $wallet->bonus_balance_usd = $bonusAfter;
        $wallet->lifetime_wagered_usd = max(0, (float) $wallet->lifetime_wagered_usd - (float) $round->bet_amount_usd);
        $wallet->save();

        $round->status = 'rolled_back';
        $round->outcome_type = 'rolled_back';
        $round->save();

        if ($refundCash > 0) {
            WalletLedgerEntry::create([
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'entry_reference' => 'LED-RBK-CASH-' . strtoupper(Str::random(10)),
                'idempotency_key' => 'idemp-rbk-c-' . $round->round_code,
                'entry_type' => 'game_rollback_credit',
                'balance_bucket' => 'cash',
                'amount_usd' => $refundCash,
                'cash_balance_before_usd' => $cashBefore,
                'cash_balance_after_usd' => $cashAfter,
                'bonus_balance_before_usd' => $bonusBefore,
                'bonus_balance_after_usd' => $bonusBefore,
                'locked_balance_before_usd' => $lockedBefore,
                'locked_balance_after_usd' => $lockedBefore,
                'related_type' => GameRound::class,
                'related_id' => $round->id,
                'description_en' => "Atomic Bet Rollback Refund for {$round->game->title} (Round #{$round->round_code})",
                'description_id' => "Pengembalian Taruhan Rollback untuk {$round->game->title} (Ronde #{$round->round_code})",
                'actor_user_id' => $wallet->user_id,
                'created_at' => now(),
            ]);
        }

        if ($refundBonus > 0) {
            WalletLedgerEntry::create([
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'entry_reference' => 'LED-RBK-BONUS-' . strtoupper(Str::random(10)),
                'idempotency_key' => 'idemp-rbk-b-' . $round->round_code,
                'entry_type' => 'game_rollback_credit',
                'balance_bucket' => 'bonus',
                'amount_usd' => $refundBonus,
                'cash_balance_before_usd' => $cashAfter,
                'cash_balance_after_usd' => $cashAfter,
                'bonus_balance_before_usd' => $bonusBefore,
                'bonus_balance_after_usd' => $bonusAfter,
                'locked_balance_before_usd' => $lockedBefore,
                'locked_balance_after_usd' => $lockedBefore,
                'related_type' => GameRound::class,
                'related_id' => $round->id,
                'description_en' => "Atomic Bonus Bet Rollback Refund for {$round->game->title} (Round #{$round->round_code})",
                'description_id' => "Pengembalian Taruhan Bonus Rollback untuk {$round->game->title} (Ronde #{$round->round_code})",
                'actor_user_id' => $wallet->user_id,
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Lock funds from cash into locked_withdrawal when requesting a withdrawal.
     */
    public function lockForWithdrawal(Wallet $wallet, float $amount, PaymentTransaction $transaction): void
    {
        $cashBefore = (float) $wallet->cash_balance_usd;
        $bonusBefore = (float) $wallet->bonus_balance_usd;
        $lockedBefore = (float) $wallet->locked_withdrawal_usd;

        if ($cashBefore < $amount) {
            throw new InvalidArgumentException('Insufficient cash balance to request this withdrawal.');
        }

        $cashAfter = $cashBefore - $amount;
        $lockedAfter = $lockedBefore + $amount;

        $wallet->cash_balance_usd = $cashAfter;
        $wallet->locked_withdrawal_usd = $lockedAfter;
        $wallet->save();

        // 1. Cash debit entry
        WalletLedgerEntry::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'entry_reference' => 'LED-WDR-LOCK-CASH-' . strtoupper(Str::random(8)),
            'idempotency_key' => 'idemp-wdr-lock-c-' . $transaction->reference,
            'entry_type' => 'withdrawal_lock',
            'balance_bucket' => 'cash',
            'amount_usd' => -$amount,
            'cash_balance_before_usd' => $cashBefore,
            'cash_balance_after_usd' => $cashAfter,
            'bonus_balance_before_usd' => $bonusBefore,
            'bonus_balance_after_usd' => $bonusBefore,
            'locked_balance_before_usd' => $lockedBefore,
            'locked_balance_after_usd' => $lockedAfter,
            'related_type' => PaymentTransaction::class,
            'related_id' => $transaction->id,
            'description_en' => "Cash Locked for Withdrawal Request #{$transaction->reference}",
            'description_id' => "Saldo Tunai Ditahan untuk Penarikan #{$transaction->reference}",
            'actor_user_id' => $wallet->user_id,
            'created_at' => now(),
        ]);

        // 2. Locked withdrawal credit entry
        WalletLedgerEntry::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'entry_reference' => 'LED-WDR-LOCK-HOLD-' . strtoupper(Str::random(8)),
            'idempotency_key' => 'idemp-wdr-lock-h-' . $transaction->reference,
            'entry_type' => 'withdrawal_lock',
            'balance_bucket' => 'locked_withdrawal',
            'amount_usd' => $amount,
            'cash_balance_before_usd' => $cashBefore,
            'cash_balance_after_usd' => $cashAfter,
            'bonus_balance_before_usd' => $bonusBefore,
            'bonus_balance_after_usd' => $bonusBefore,
            'locked_balance_before_usd' => $lockedBefore,
            'locked_balance_after_usd' => $lockedAfter,
            'related_type' => PaymentTransaction::class,
            'related_id' => $transaction->id,
            'description_en' => "Funds In-Transit Locked for Review #{$transaction->reference}",
            'description_id' => "Dana Tertahan dalam Proses Tinjauan #{$transaction->reference}",
            'actor_user_id' => $wallet->user_id,
            'created_at' => now(),
        ]);
    }

    /**
     * Cancel pending withdrawal by user and refund locked funds back to cash.
     */
    public function cancelWithdrawal(Wallet $wallet, PaymentTransaction $transaction): void
    {
        $cashBefore = (float) $wallet->cash_balance_usd;
        $bonusBefore = (float) $wallet->bonus_balance_usd;
        $lockedBefore = (float) $wallet->locked_withdrawal_usd;
        $amount = (float) $transaction->amount_usd;

        $cashAfter = $cashBefore + $amount;
        $lockedAfter = max(0, $lockedBefore - $amount);

        $wallet->cash_balance_usd = $cashAfter;
        $wallet->locked_withdrawal_usd = $lockedAfter;
        $wallet->save();

        $transaction->status = 'cancelled';
        $transaction->completed_at = now();
        $transaction->save();

        // 1. Release from locked bucket
        WalletLedgerEntry::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'entry_reference' => 'LED-WDR-CNCL-HOLD-' . strtoupper(Str::random(8)),
            'idempotency_key' => 'idemp-wdr-cncl-h-' . $transaction->reference,
            'entry_type' => 'withdrawal_cancel_refund',
            'balance_bucket' => 'locked_withdrawal',
            'amount_usd' => -$amount,
            'cash_balance_before_usd' => $cashBefore,
            'cash_balance_after_usd' => $cashAfter,
            'bonus_balance_before_usd' => $bonusBefore,
            'bonus_balance_after_usd' => $bonusBefore,
            'locked_balance_before_usd' => $lockedBefore,
            'locked_balance_after_usd' => $lockedAfter,
            'related_type' => PaymentTransaction::class,
            'related_id' => $transaction->id,
            'description_en' => "Released Locked Hold #{$transaction->reference}",
            'description_id' => "Pelepasan Dana Tertahan #{$transaction->reference}",
            'actor_user_id' => $wallet->user_id,
            'created_at' => now(),
        ]);

        // 2. Restore to cash bucket
        WalletLedgerEntry::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'entry_reference' => 'LED-WDR-CNCL-CASH-' . strtoupper(Str::random(8)),
            'idempotency_key' => 'idemp-wdr-cncl-c-' . $transaction->reference,
            'entry_type' => 'withdrawal_cancel_refund',
            'balance_bucket' => 'cash',
            'amount_usd' => $amount,
            'cash_balance_before_usd' => $cashBefore,
            'cash_balance_after_usd' => $cashAfter,
            'bonus_balance_before_usd' => $bonusBefore,
            'bonus_balance_after_usd' => $bonusBefore,
            'locked_balance_before_usd' => $lockedBefore,
            'locked_balance_after_usd' => $lockedAfter,
            'related_type' => PaymentTransaction::class,
            'related_id' => $transaction->id,
            'description_en' => "Restored Cash Balance for Cancelled Withdrawal #{$transaction->reference}",
            'description_id' => "Pengembalian Saldo Tunai Penarikan Dibatalkan #{$transaction->reference}",
            'actor_user_id' => $wallet->user_id,
            'created_at' => now(),
        ]);
    }

    /**
     * Finalize approved withdrawal (deduct from locked_withdrawal).
     */
    public function finalizeWithdrawal(Wallet $wallet, PaymentTransaction $transaction, User $reviewer, ?string $note = null): void
    {
        $cashBefore = (float) $wallet->cash_balance_usd;
        $bonusBefore = (float) $wallet->bonus_balance_usd;
        $lockedBefore = (float) $wallet->locked_withdrawal_usd;
        $amount = (float) $transaction->amount_usd;

        $lockedAfter = max(0, $lockedBefore - $amount);
        $wallet->locked_withdrawal_usd = $lockedAfter;
        $wallet->lifetime_withdrawn_usd = (float) $wallet->lifetime_withdrawn_usd + $amount;
        $wallet->save();

        $transaction->status = 'paid';
        $transaction->reviewed_by_user_id = $reviewer->id;
        $transaction->review_note = $note;
        $transaction->reviewed_at = now();
        $transaction->completed_at = now();
        $transaction->save();

        WalletLedgerEntry::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'entry_reference' => 'LED-WDR-FIN-' . strtoupper(Str::random(10)),
            'idempotency_key' => 'idemp-wdr-fin-' . $transaction->reference,
            'entry_type' => 'withdrawal_finalized',
            'balance_bucket' => 'locked_withdrawal',
            'amount_usd' => -$amount,
            'cash_balance_before_usd' => $cashBefore,
            'cash_balance_after_usd' => $cashBefore,
            'bonus_balance_before_usd' => $bonusBefore,
            'bonus_balance_after_usd' => $bonusBefore,
            'locked_balance_before_usd' => $lockedBefore,
            'locked_balance_after_usd' => $lockedAfter,
            'related_type' => PaymentTransaction::class,
            'related_id' => $transaction->id,
            'description_en' => "Withdrawal #{$transaction->reference} Approved & Paid Out",
            'description_id' => "Penarikan #{$transaction->reference} Disetujui & Dibayarkan",
            'actor_user_id' => $reviewer->id,
            'created_at' => now(),
        ]);

        $this->auditLogger->log(
            'withdrawal.approved',
            $reviewer,
            PaymentTransaction::class,
            $transaction->id,
            $note ?? 'Approved by administrative review',
            ['status' => 'pending_review'],
            ['status' => 'paid', 'amount_usd' => $amount]
        );
    }

    /**
     * Reject withdrawal and refund locked funds back to cash balance.
     */
    public function rejectWithdrawal(Wallet $wallet, PaymentTransaction $transaction, User $reviewer, string $reason): void
    {
        $cashBefore = (float) $wallet->cash_balance_usd;
        $bonusBefore = (float) $wallet->bonus_balance_usd;
        $lockedBefore = (float) $wallet->locked_withdrawal_usd;
        $amount = (float) $transaction->amount_usd;

        $cashAfter = $cashBefore + $amount;
        $lockedAfter = max(0, $lockedBefore - $amount);

        $wallet->cash_balance_usd = $cashAfter;
        $wallet->locked_withdrawal_usd = $lockedAfter;
        $wallet->save();

        $transaction->status = 'rejected';
        $transaction->reviewed_by_user_id = $reviewer->id;
        $transaction->review_note = $reason;
        $transaction->reviewed_at = now();
        $transaction->completed_at = now();
        $transaction->save();

        // 1. Release from locked bucket
        WalletLedgerEntry::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'entry_reference' => 'LED-WDR-REJ-HOLD-' . strtoupper(Str::random(8)),
            'idempotency_key' => 'idemp-wdr-rej-h-' . $transaction->reference,
            'entry_type' => 'withdrawal_rejected_refund',
            'balance_bucket' => 'locked_withdrawal',
            'amount_usd' => -$amount,
            'cash_balance_before_usd' => $cashBefore,
            'cash_balance_after_usd' => $cashAfter,
            'bonus_balance_before_usd' => $bonusBefore,
            'bonus_balance_after_usd' => $bonusBefore,
            'locked_balance_before_usd' => $lockedBefore,
            'locked_balance_after_usd' => $lockedAfter,
            'related_type' => PaymentTransaction::class,
            'related_id' => $transaction->id,
            'description_en' => "Released Locked Hold on Rejection #{$transaction->reference}",
            'description_id' => "Pelepasan Dana Tertahan atas Penolakan #{$transaction->reference}",
            'actor_user_id' => $reviewer->id,
            'created_at' => now(),
        ]);

        // 2. Refund to cash bucket
        WalletLedgerEntry::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'entry_reference' => 'LED-WDR-REJ-CASH-' . strtoupper(Str::random(8)),
            'idempotency_key' => 'idemp-wdr-rej-c-' . $transaction->reference,
            'entry_type' => 'withdrawal_rejected_refund',
            'balance_bucket' => 'cash',
            'amount_usd' => $amount,
            'cash_balance_before_usd' => $cashBefore,
            'cash_balance_after_usd' => $cashAfter,
            'bonus_balance_before_usd' => $bonusBefore,
            'bonus_balance_after_usd' => $bonusBefore,
            'locked_balance_before_usd' => $lockedBefore,
            'locked_balance_after_usd' => $lockedAfter,
            'related_type' => PaymentTransaction::class,
            'related_id' => $transaction->id,
            'description_en' => "Withdrawal #{$transaction->reference} Rejected: {$reason} (Funds Refunded)",
            'description_id' => "Penarikan #{$transaction->reference} Ditolak: {$reason} (Dana Dikembalikan)",
            'actor_user_id' => $reviewer->id,
            'created_at' => now(),
        ]);

        $this->auditLogger->log(
            'withdrawal.rejected',
            $reviewer,
            PaymentTransaction::class,
            $transaction->id,
            $reason,
            ['status' => 'pending_review'],
            ['status' => 'rejected', 'refund_usd' => $amount]
        );
    }

    /**
     * Convert completed bonus into real cash.
     */
    public function convertBonusToCash(Wallet $wallet, PlayerBonus $bonus): void
    {
        $cashBefore = (float) $wallet->cash_balance_usd;
        $bonusBefore = (float) $wallet->bonus_balance_usd;
        $lockedBefore = (float) $wallet->locked_withdrawal_usd;
        $convertAmount = $bonusBefore;

        $cashAfter = $cashBefore + $convertAmount;
        $bonusAfter = 0.00;

        $wallet->cash_balance_usd = $cashAfter;
        $wallet->bonus_balance_usd = $bonusAfter;
        $wallet->save();

        $bonus->status = 'completed';
        $bonus->completed_at = now();
        $bonus->save();

        // 1. Debit from bonus bucket
        WalletLedgerEntry::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'entry_reference' => 'LED-BNS-CNV-DEB-' . strtoupper(Str::random(8)),
            'idempotency_key' => 'idemp-bns-cnv-d-' . $bonus->id . '-' . time(),
            'entry_type' => 'bonus_converted_cash_credit',
            'balance_bucket' => 'bonus',
            'amount_usd' => -$convertAmount,
            'cash_balance_before_usd' => $cashBefore,
            'cash_balance_after_usd' => $cashAfter,
            'bonus_balance_before_usd' => $bonusBefore,
            'bonus_balance_after_usd' => $bonusAfter,
            'locked_balance_before_usd' => $lockedBefore,
            'locked_balance_after_usd' => $lockedBefore,
            'related_type' => PlayerBonus::class,
            'related_id' => $bonus->id,
            'description_en' => "Wagering Requirement Met for {$bonus->promotion->title_en} — Bonus Converted",
            'description_id' => "Persyaratan Rollover Terpenuhi untuk {$bonus->promotion->title_id} — Bonus Dikonversi",
            'actor_user_id' => $wallet->user_id,
            'created_at' => now(),
        ]);

        // 2. Credit to cash bucket
        WalletLedgerEntry::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'entry_reference' => 'LED-BNS-CNV-CRD-' . strtoupper(Str::random(8)),
            'idempotency_key' => 'idemp-bns-cnv-c-' . $bonus->id . '-' . time(),
            'entry_type' => 'bonus_converted_cash_credit',
            'balance_bucket' => 'cash',
            'amount_usd' => $convertAmount,
            'cash_balance_before_usd' => $cashBefore,
            'cash_balance_after_usd' => $cashAfter,
            'bonus_balance_before_usd' => $bonusBefore,
            'bonus_balance_after_usd' => $bonusAfter,
            'locked_balance_before_usd' => $lockedBefore,
            'locked_balance_after_usd' => $lockedBefore,
            'related_type' => PlayerBonus::class,
            'related_id' => $bonus->id,
            'description_en' => "Wagering Requirement Met for {$bonus->promotion->title_en} — Converted to Cash",
            'description_id' => "Persyaratan Rollover Terpenuhi untuk {$bonus->promotion->title_id} — Dikonversi ke Tunai",
            'actor_user_id' => $wallet->user_id,
            'created_at' => now(),
        ]);
    }

    /**
     * Forfeit active bonus balance.
     */
    public function forfeitBonus(Wallet $wallet, PlayerBonus $bonus): void
    {
        $cashBefore = (float) $wallet->cash_balance_usd;
        $bonusBefore = (float) $wallet->bonus_balance_usd;
        $lockedBefore = (float) $wallet->locked_withdrawal_usd;

        $wallet->bonus_balance_usd = 0.00;
        $wallet->save();

        $bonus->status = 'forfeited';
        $bonus->completed_at = now();
        $bonus->save();

        WalletLedgerEntry::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'entry_reference' => 'LED-BNS-FORF-' . strtoupper(Str::random(10)),
            'idempotency_key' => 'idemp-bns-forf-' . $bonus->id . '-' . time(),
            'entry_type' => 'bonus_forfeited_debit',
            'balance_bucket' => 'bonus',
            'amount_usd' => -$bonusBefore,
            'cash_balance_before_usd' => $cashBefore,
            'cash_balance_after_usd' => $cashBefore,
            'bonus_balance_before_usd' => $bonusBefore,
            'bonus_balance_after_usd' => 0.00,
            'locked_balance_before_usd' => $lockedBefore,
            'locked_balance_after_usd' => $lockedBefore,
            'related_type' => PlayerBonus::class,
            'related_id' => $bonus->id,
            'description_en' => "Player Forfeited Active Bonus ({$bonus->promotion->title_en})",
            'description_id' => "Pemain Membatalkan Bonus Aktif ({$bonus->promotion->title_id})",
            'actor_user_id' => $wallet->user_id,
            'created_at' => now(),
        ]);
    }

    /**
     * Credit VIP Cashback in USD.
     */
    public function creditVipCashback(Wallet $wallet, float $amount): void
    {
        $cashBefore = (float) $wallet->cash_balance_usd;
        $bonusBefore = (float) $wallet->bonus_balance_usd;
        $lockedBefore = (float) $wallet->locked_withdrawal_usd;

        $cashAfter = $cashBefore + $amount;
        $wallet->cash_balance_usd = $cashAfter;
        $wallet->save();

        WalletLedgerEntry::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'entry_reference' => 'LED-VIP-CB-' . strtoupper(Str::random(10)),
            'idempotency_key' => 'idemp-vip-cb-' . $wallet->id . '-' . now()->format('Y-W'),
            'entry_type' => 'vip_cashback_credit',
            'balance_bucket' => 'cash',
            'amount_usd' => $amount,
            'cash_balance_before_usd' => $cashBefore,
            'cash_balance_after_usd' => $cashAfter,
            'bonus_balance_before_usd' => $bonusBefore,
            'bonus_balance_after_usd' => $bonusBefore,
            'locked_balance_before_usd' => $lockedBefore,
            'locked_balance_after_usd' => $lockedBefore,
            'description_en' => "VIP Weekly Cashback Claimed ({$wallet->user->vipTier?->name_en})",
            'description_id' => "Klaim Cashback Mingguan VIP ({$wallet->user->vipTier?->name_id})",
            'actor_user_id' => $wallet->user_id,
            'created_at' => now(),
        ]);
    }

    /**
     * Manual USD balance adjustment by Super Admin.
     */
    public function manualAdminAdjust(
        Wallet $wallet,
        string $bucket, // 'cash' or 'bonus'
        string $action, // 'credit' or 'debit'
        float $amount,
        string $reason,
        User $admin
    ): void {
        $cashBefore = (float) $wallet->cash_balance_usd;
        $bonusBefore = (float) $wallet->bonus_balance_usd;
        $lockedBefore = (float) $wallet->locked_withdrawal_usd;

        $signedAmount = $action === 'credit' ? $amount : -$amount;

        if ($bucket === 'cash') {
            $cashAfter = $cashBefore + $signedAmount;
            if ($cashAfter < 0) {
                throw new InvalidArgumentException('Debit exceeds available cash balance.');
            }
            $bonusAfter = $bonusBefore;
            $wallet->cash_balance_usd = $cashAfter;
        } else {
            $bonusAfter = $bonusBefore + $signedAmount;
            if ($bonusAfter < 0) {
                throw new InvalidArgumentException('Debit exceeds available bonus balance.');
            }
            $cashAfter = $cashBefore;
            $wallet->bonus_balance_usd = $bonusAfter;
        }

        $wallet->save();

        WalletLedgerEntry::create([
            'wallet_id' => $wallet->id,
            'user_id' => $wallet->user_id,
            'entry_reference' => 'LED-MAN-ADJ-' . strtoupper(Str::random(10)),
            'idempotency_key' => 'idemp-man-adj-' . Str::uuid(),
            'entry_type' => 'manual_admin_adjustment',
            'balance_bucket' => $bucket,
            'amount_usd' => $signedAmount,
            'cash_balance_before_usd' => $cashBefore,
            'cash_balance_after_usd' => $cashAfter,
            'bonus_balance_before_usd' => $bonusBefore,
            'bonus_balance_after_usd' => $bonusAfter,
            'locked_balance_before_usd' => $lockedBefore,
            'locked_balance_after_usd' => $lockedBefore,
            'description_en' => "Manual Admin Adjustment by {$admin->name}: {$reason}",
            'description_id' => "Penyesuaian Saldo Manual oleh {$admin->name}: {$reason}",
            'actor_user_id' => $admin->id,
            'created_at' => now(),
        ]);

        $this->auditLogger->log(
            'wallet.manual_adjustment',
            $admin,
            Wallet::class,
            $wallet->id,
            $reason,
            ['cash' => $cashBefore, 'bonus' => $bonusBefore],
            ['cash' => $cashAfter, 'bonus' => $bonusAfter, 'bucket' => $bucket, 'action' => $action, 'amount' => $amount]
        );
    }
}
