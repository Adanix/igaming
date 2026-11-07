<?php

namespace App\Services;

use App\Models\Game;
use App\Models\PaymentTransaction;
use App\Models\PlayerBonus;
use App\Models\Promotion;
use App\Models\User;
use App\Models\VipTier;
use App\Models\WalletLedgerEntry;
use Illuminate\Support\Str;

class BonusRolloverService
{
    /**
     * Attach a promotional bonus to player's account.
     */
    public function attachPromotion(
        User $user,
        Promotion $promotion,
        float $depositAmount,
        ?PaymentTransaction $transaction = null
    ): ?PlayerBonus {
        $wallet = $user->wallet;
        if (! $wallet) {
            return null;
        }

        // Calculate bonus amount
        $bonusAmount = 0.00;
        if ($promotion->promo_type === 'deposit_match') {
            $calc = $depositAmount * ((float) $promotion->match_percentage / 100.0);
            $bonusAmount = min((float) $promotion->max_bonus_usd, $calc);
        } elseif ($promotion->promo_type === 'free_chip') {
            $bonusAmount = (float) $promotion->fixed_bonus_usd;
        } else {
            $calc = $depositAmount * ((float) $promotion->match_percentage / 100.0);
            $bonusAmount = min((float) $promotion->max_bonus_usd, $calc);
        }

        if ($bonusAmount <= 0) {
            return null;
        }

        $wageringRequired = $bonusAmount * (float) $promotion->wagering_multiplier;

        // Credit to bonus bucket in wallet
        $cashBefore = (float) $wallet->cash_balance_usd;
        $bonusBefore = (float) $wallet->bonus_balance_usd;
        $lockedBefore = (float) $wallet->locked_withdrawal_usd;

        $bonusAfter = $bonusBefore + $bonusAmount;
        $wallet->bonus_balance_usd = $bonusAfter;
        $wallet->save();

        // Create PlayerBonus record
        $playerBonus = PlayerBonus::create([
            'user_id' => $user->id,
            'promotion_id' => $promotion->id,
            'payment_transaction_id' => $transaction?->id,
            'bonus_awarded_usd' => $bonusAmount,
            'wagering_required_usd' => $wageringRequired,
            'wagering_completed_usd' => 0.00,
            'status' => 'active',
        ]);

        // Insert ledger entry
        WalletLedgerEntry::create([
            'wallet_id' => $wallet->id,
            'user_id' => $user->id,
            'entry_reference' => 'LED-BNS-CRED-' . strtoupper(Str::random(10)),
            'idempotency_key' => 'idemp-bns-cred-' . $playerBonus->id,
            'entry_type' => 'bonus_credit',
            'balance_bucket' => 'bonus',
            'amount_usd' => $bonusAmount,
            'cash_balance_before_usd' => $cashBefore,
            'cash_balance_after_usd' => $cashBefore,
            'bonus_balance_before_usd' => $bonusBefore,
            'bonus_balance_after_usd' => $bonusAfter,
            'locked_balance_before_usd' => $lockedBefore,
            'locked_balance_after_usd' => $lockedBefore,
            'related_type' => PlayerBonus::class,
            'related_id' => $playerBonus->id,
            'description_en' => "Promotional Bonus Credited: {$promotion->title_en} ($" . number_format($bonusAmount, 2) . ")",
            'description_id' => "Bonus Promosi Dikreditkan: {$promotion->title_id} ($" . number_format($bonusAmount, 2) . ")",
            'actor_user_id' => $user->id,
            'created_at' => now(),
        ]);

        return $playerBonus;
    }

    /**
     * Increment bonus wagering progress and VIP points on every settled bet.
     */
    public function incrementRolloverOnBet(User $user, Game $game, float $betAmount): void
    {
        // 1. VIP Points increment
        $user->vip_points = (float) $user->vip_points + $betAmount;

        // Check if user qualifies for higher VIP Tier
        $nextTier = VipTier::where('min_wagered_usd', '<=', $user->vip_points)
            ->orderByDesc('min_wagered_usd')
            ->first();

        if ($nextTier && (! $user->vip_tier_id || $nextTier->min_wagered_usd > ($user->vipTier?->min_wagered_usd ?? 0))) {
            $user->vip_tier_id = $nextTier->id;
        }

        $user->save();

        // 2. Bonus rollover progress check
        $activeBonus = $user->activeBonus;
        if (! $activeBonus || $activeBonus->status !== 'active') {
            return;
        }

        $contributionRate = ((float) $game->wagering_contribution_pct) / 100.0;
        $eligibleContribution = $betAmount * $contributionRate;

        $newCompleted = (float) $activeBonus->wagering_completed_usd + $eligibleContribution;
        $activeBonus->wagering_completed_usd = $newCompleted;

        if ($newCompleted >= (float) $activeBonus->wagering_required_usd) {
            // Conversion achieved! Convert remaining bonus balance to real cash
            $wallet = $user->wallet;
            if ($wallet) {
                app(WalletLedgerService::class)->convertBonusToCash($wallet, $activeBonus);
            }
        } else {
            $activeBonus->save();
        }
    }
}
