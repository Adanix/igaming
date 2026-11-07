<?php

namespace App\Services\Simulators;

use App\Models\Game;
use App\Models\GameRound;
use App\Models\GameSession;
use App\Models\SimulatedWebhookLog;
use App\Models\User;
use App\Models\Wallet;
use App\Services\BonusRolloverService;
use App\Services\WalletLedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SimulatedGameAggregatorService
{
    public function __construct(
        protected WalletLedgerService $ledgerService,
        protected BonusRolloverService $bonusService
    ) {}

    /**
     * Launch or retrieve active game session for user.
     */
    public function getOrCreateSession(User $user, Game $game): GameSession
    {
        $session = GameSession::firstOrCreate(
            [
                'user_id' => $user->id,
                'game_id' => $game->id,
                'status' => 'active',
            ],
            [
                'session_token' => 'GS-' . Str::random(40),
                'currency' => 'USD',
                'started_at' => now(),
                'last_activity_at' => now(),
            ]
        );

        $session->update(['last_activity_at' => now()]);
        return $session;
    }

    /**
     * Execute a simulated round with deterministic or random outcome.
     */
    public function playRound(Game $game, User $user, float $betAmount, string $outcomeMode = 'random'): array
    {
        if (! $game->isActive()) {
            throw new InvalidArgumentException("Game '{$game->title}' is currently under maintenance.");
        }

        if ($betAmount < (float) $game->min_bet_usd || $betAmount > (float) $game->max_bet_usd) {
            throw new InvalidArgumentException("Bet amount must be between $" . number_format($game->min_bet_usd, 2) . " and $" . number_format($game->max_bet_usd, 2) . " USD.");
        }

        $session = $this->getOrCreateSession($user, $game);
        $roundCode = 'RND-' . date('Ymd') . '-' . strtoupper(Str::random(8));
        $idempotencyKey = 'idemp-rnd-' . $roundCode;

        // Determine multiplier and outcome type
        $multiplier = 0.00;
        $outcomeType = 'loss';

        if ($outcomeMode === 'loss') {
            $multiplier = 0.00;
            $outcomeType = 'loss';
        } elseif ($outcomeMode === 'win_small') {
            $multiplier = 2.50;
            $outcomeType = 'win';
        } elseif ($outcomeMode === 'win_big') {
            $multiplier = 15.00;
            $outcomeType = 'big_win';
        } elseif ($outcomeMode === 'rollback') {
            $multiplier = 0.00;
            $outcomeType = 'rolled_back';
        } else {
            // Random based on RTP
            $rand = mt_rand(1, 100);
            if ($rand <= 58) {
                $multiplier = 0.00;
                $outcomeType = 'loss';
            } elseif ($rand <= 88) {
                $multiplier = round(mt_rand(12, 35) / 10, 2);
                $outcomeType = 'win';
            } else {
                $multiplier = round(mt_rand(50, 200) / 10, 2);
                $outcomeType = 'big_win';
            }
        }

        $payoutAmount = round($betAmount * $multiplier, 2);

        // Build simulated external webhook payload
        $payload = [
            'event' => 'game.round_settled',
            'session_token' => $session->session_token,
            'player_id' => $user->id,
            'game_code' => $game->game_code,
            'round_code' => $roundCode,
            'currency' => 'USD',
            'bet_amount_usd' => $betAmount,
            'payout_amount_usd' => $payoutAmount,
            'multiplier' => $multiplier,
            'outcome' => $outcomeType,
            'timestamp' => now()->toIso8601String(),
        ];

        $payloadJson = json_encode($payload);
        $secret = config('demo.webhook_secret');
        $signature = hash_hmac('sha256', $payloadJson, $secret);

        // Execute atomic database writes
        $round = DB::transaction(function () use (
            $session,
            $user,
            $game,
            $roundCode,
            $idempotencyKey,
            $betAmount,
            $payoutAmount,
            $multiplier,
            $outcomeType,
            $outcomeMode,
            $signature
        ) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->firstOrFail();

            // 1. Create round record
            $round = GameRound::create([
                'game_session_id' => $session->id,
                'user_id' => $user->id,
                'game_id' => $game->id,
                'round_code' => $roundCode,
                'idempotency_key' => $idempotencyKey,
                'bet_amount_usd' => $betAmount,
                'cash_bet_component_usd' => 0.00,
                'bonus_bet_component_usd' => 0.00,
                'payout_amount_usd' => $payoutAmount,
                'multiplier' => $multiplier,
                'outcome_type' => $outcomeType,
                'status' => 'bet_placed',
                'provider_signature' => $signature,
                'settled_at' => now(),
            ]);

            // 2. Debit Bet
            $this->ledgerService->debitGameBet($wallet, $betAmount, $round);

            if ($outcomeMode === 'rollback') {
                // Simulate atomic rollback immediately
                $this->ledgerService->rollbackGameBet($wallet, $round);
            } else {
                // 3. Credit Win if any
                if ($payoutAmount > 0) {
                    $this->ledgerService->creditGameWin($wallet, $payoutAmount, $round);
                }

                $round->status = 'settled';
                $round->save();

                // 4. Increment bonus rollover & VIP points
                $this->bonusService->incrementRolloverOnBet($user, $game, $betAmount);
            }

            return $round;
        });

        // Log simulated webhook
        SimulatedWebhookLog::create([
            'source_type' => 'game_aggregator',
            'event_name' => 'round.settle',
            'reference' => $roundCode,
            'idempotency_key' => $idempotencyKey,
            'signature_header' => $signature,
            'signature_valid' => true,
            'processing_outcome' => 'processed',
            'payload_json' => $payload,
            'response_json' => [
                'status' => 'OK',
                'round_code' => $roundCode,
                'balance_usd' => $user->wallet->fresh()->cash_balance_usd,
            ],
            'created_at' => now(),
        ]);

        $freshWallet = $user->wallet->fresh();

        return [
            'round_code' => $round->round_code,
            'bet_amount_usd' => (float) $round->bet_amount_usd,
            'payout_amount_usd' => (float) $round->payout_amount_usd,
            'multiplier' => (float) $round->multiplier,
            'outcome_type' => $round->outcome_type,
            'status' => $round->status,
            'cash_balance_usd' => (float) $freshWallet->cash_balance_usd,
            'bonus_balance_usd' => (float) $freshWallet->bonus_balance_usd,
            'total_playable_usd' => $freshWallet->totalPlayableUsd(),
            'wagering_progress_pct' => $user->activeBonus?->progressPercentage() ?? 100.0,
            'webhook_signature' => $signature,
            'webhook_payload' => $payload,
        ];
    }
}
