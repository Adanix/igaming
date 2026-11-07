<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\PlayerBonus;
use App\Models\Promotion;
use App\Services\BonusRolloverService;
use App\Services\WalletLedgerService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function __construct(
        protected BonusRolloverService $bonusService,
        protected WalletLedgerService $ledgerService
    ) {}

    /**
     * Display available promotions and current rollover status.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $promotions = Promotion::where('status', 'active')
            ->orderBy('id')
            ->get();

        $activeBonus = PlayerBonus::with('promotion')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        $bonusHistory = PlayerBonus::with('promotion')
            ->where('user_id', $user->id)
            ->where('status', '!=', 'active')
            ->latest('id')
            ->limit(10)
            ->get();

        return view('player.promotions', compact('promotions', 'activeBonus', 'bonusHistory'));
    }

    /**
     * Claim an available promotion.
     */
    public function claim(int $id, Request $request): RedirectResponse
    {
        $user = $request->user();
        $promotion = Promotion::where('id', $id)->where('status', 'active')->firstOrFail();

        $currentActive = PlayerBonus::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($currentActive) {
            return redirect()->back()
                ->with('error', 'You already have an active bonus in progress. Complete rollover or forfeit it first.');
        }

        try {
            // In demo mode, use a simulated qualifying base (e.g. $50 deposit base or fixed bonus)
            $simulatedDepositBase = 50.00;
            $bonus = $this->bonusService->attachPromotion($user, $promotion, $simulatedDepositBase);

            if (! $bonus) {
                return redirect()->back()->with('error', 'Unable to award this bonus at this time.');
            }

            return redirect()->route('promotions.index')
                ->with('status', "Bonus awarded! Added " . format_usd($bonus->bonus_awarded_usd) . " to bonus balance with $" . number_format($bonus->wagering_required_usd, 2) . " wagering requirement.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Forfeit an active bonus.
     */
    public function forfeit(int $id, Request $request): RedirectResponse
    {
        $user = $request->user();
        $bonus = PlayerBonus::where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->firstOrFail();

        try {
            $this->ledgerService->forfeitBonus($user->wallet, $bonus);

            return redirect()->route('promotions.index')
                ->with('status', 'Active bonus has been forfeited. Bonus balance cleared.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
