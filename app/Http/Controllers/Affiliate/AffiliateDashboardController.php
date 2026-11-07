<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommission;
use App\Models\AffiliateProfile;
use App\Services\AffiliateCommissionService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AffiliateDashboardController extends Controller
{
    public function __construct(
        protected AffiliateCommissionService $commissionService
    ) {}

    /**
     * Display Affiliate Partner CRM, NGR breakdown, and referral cohort.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $affiliate = AffiliateProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'referral_code' => 'NEXUS-' . strtoupper(Str::random(5)),
                'revshare_percentage' => 35.00,
                'commission_balance_usd' => 0.00,
                'lifetime_earned_usd' => 0.00,
                'lifetime_paid_usd' => 0.00,
            ]
        );

        $referrals = $affiliate->referrals()
            ->with('referredUser.wallet')
            ->orderByDesc('id')
            ->get();

        $commissions = AffiliateCommission::where('affiliate_profile_id', $affiliate->id)
            ->latest('id')
            ->limit(10)
            ->get();

        return view('affiliate.dashboard', compact('affiliate', 'referrals', 'commissions'));
    }

    /**
     * Trigger live recalculation of NGR and commissions across all referred players.
     */
    public function recalculate(Request $request): RedirectResponse
    {
        $user = $request->user();
        $affiliate = AffiliateProfile::where('user_id', $user->id)->firstOrFail();

        try {
            $stats = $this->commissionService->recalculate($affiliate);

            return redirect()->route('affiliate.dashboard')
                ->with('status', "NGR & Commissions recalculated across {$stats['total_referrals']} players. Live balance: " . format_usd($stats['commission_balance_usd']));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Request commission payout.
     */
    public function requestPayout(Request $request): RedirectResponse
    {
        $user = $request->user();
        $affiliate = AffiliateProfile::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'amount_usd' => ['required', 'numeric', 'min:50.00'],
            'payout_method' => ['required', 'string'],
            'payout_details' => ['required', 'string'],
        ]);

        $amount = (float) $validated['amount_usd'];
        $currentBalance = (float) $affiliate->commission_balance_usd;

        if ($amount > $currentBalance) {
            return redirect()->back()
                ->with('error', "Requested payout " . format_usd($amount) . " exceeds available commission balance of " . format_usd($currentBalance));
        }

        $affiliate->commission_balance_usd = max(0.00, $currentBalance - $amount);
        $affiliate->lifetime_paid_usd = (float) $affiliate->lifetime_paid_usd + $amount;
        $affiliate->save();

        AffiliateCommission::create([
            'affiliate_profile_id' => $affiliate->id,
            'period_label' => date('Y-m') . ' Payout',
            'gross_ggr_usd' => 0.00,
            'net_ngr_usd' => 0.00,
            'revshare_pct' => (float) $affiliate->revshare_percentage,
            'commission_amount_usd' => $amount,
            'status' => 'paid_out',
        ]);

        return redirect()->route('affiliate.dashboard')
            ->with('status', "Commission payout of " . format_usd($amount) . " has been issued to {$validated['payout_details']}.");
    }
}
