<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\GameRound;
use App\Models\VipTier;
use App\Models\WalletLedgerEntry;
use App\Services\WalletLedgerService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VipController extends Controller
{
    public function __construct(
        protected WalletLedgerService $ledgerService
    ) {}

    /**
     * Display VIP status, benefits matrix, and cashback claim.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $vipTiers = VipTier::orderBy('min_wagered_usd')->get();
        $currentTier = $user->vipTier ?? $vipTiers->first();

        // Calculate next tier & progress
        $nextTier = VipTier::where('min_wagered_usd', '>', (float) $currentTier->min_wagered_usd)
            ->orderBy('min_wagered_usd')
            ->first();

        $wageredPoints = (float) $user->vip_points;
        $progressPct = 100.0;

        if ($nextTier) {
            $currentBase = (float) $currentTier->min_wagered_usd;
            $nextTarget = (float) $nextTier->min_wagered_usd;
            $range = max(1.0, $nextTarget - $currentBase);
            $earnedInRange = max(0.0, $wageredPoints - $currentBase);
            $progressPct = min(100.0, round(($earnedInRange / $range) * 100.0, 1));
        }

        $idempKey = 'idemp-vip-cb-' . ($user->wallet?->id ?? 0) . '-' . now()->format('Y-W');
        $hasClaimedThisWeek = WalletLedgerEntry::where('idempotency_key', $idempKey)->exists();

        if ($hasClaimedThisWeek) {
            $eligibleCashback = 0.00;
        } else {
            // Calculate simulated weekly cashback
            $cashbackRate = (float) ($currentTier?->cashback_rate_pct ?? 2.0) / 100.0;
            $recentLosses = (float) GameRound::where('user_id', $user->id)
                ->where('status', 'settled')
                ->where('outcome_type', 'loss')
                ->sum('bet_amount_usd');

            $eligibleCashback = round($recentLosses * $cashbackRate, 2);
            if ($eligibleCashback <= 0.00) {
                $eligibleCashback = 15.00;
            }
        }

        return view('player.vip', compact(
            'user',
            'vipTiers',
            'currentTier',
            'nextTier',
            'progressPct',
            'eligibleCashback',
            'hasClaimedThisWeek'
        ));
    }

    /**
     * Claim VIP cashback into cash balance.
     */
    public function claimCashback(Request $request): RedirectResponse
    {
        $user = $request->user();
        $wallet = $user->wallet;

        if (! $wallet) {
            return redirect()->back()->with('error', 'Wallet not found.');
        }

        $idempKey = 'idemp-vip-cb-' . $wallet->id . '-' . now()->format('Y-W');
        if (WalletLedgerEntry::where('idempotency_key', $idempKey)->exists()) {
            return redirect()->route('vip.index')
                ->with('error', 'VIP weekly cashback for this period has already been claimed. Next cashback opens next week.');
        }

        $currentTier = $user->vipTier;
        $cashbackRate = (float) ($currentTier?->cashback_rate_pct ?? 2.0) / 100.0;

        $recentLosses = (float) GameRound::where('user_id', $user->id)
            ->where('status', 'settled')
            ->where('outcome_type', 'loss')
            ->sum('bet_amount_usd');

        $amount = round($recentLosses * $cashbackRate, 2);

        if ($amount <= 0.00) {
            // Provide a minimum demo cashback reward if $0
            $amount = 15.00;
        }

        try {
            $this->ledgerService->creditVipCashback($wallet, $amount);

            return redirect()->route('vip.index')
                ->with('status', "VIP Cashback of " . format_usd($amount) . " credited directly to your cash wallet!");
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
