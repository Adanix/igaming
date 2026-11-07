<?php

namespace App\Services;

use App\Models\AffiliateProfile;
use App\Models\AffiliateReferral;
use App\Models\GameRound;
use App\Models\PlayerBonus;
use Illuminate\Support\Facades\DB;

class AffiliateCommissionService
{
    /**
     * Recalculate live GGR, NGR, and commissions for an affiliate profile based on real player activity.
     */
    public function recalculate(AffiliateProfile $affiliate): array
    {
        return DB::transaction(function () use ($affiliate) {
            $referrals = $affiliate->referrals()->with('referredUser')->get();
            $revsharePct = (float) $affiliate->revshare_percentage;

            $totalGgr = 0.00;
            $totalNgr = 0.00;
            $totalCommission = 0.00;

            foreach ($referrals as $referral) {
                $userId = $referral->referred_user_id;

                // Aggregate settled rounds
                $rounds = GameRound::where('user_id', $userId)
                    ->where('status', 'settled')
                    ->selectRaw('COALESCE(SUM(bet_amount_usd), 0) as total_bets, COALESCE(SUM(payout_amount_usd), 0) as total_wins')
                    ->first();

                $totalBets = (float) ($rounds->total_bets ?? 0.00);
                $totalWins = (float) ($rounds->total_wins ?? 0.00);
                $ggr = $totalBets - $totalWins;

                // Converted bonus costs
                $bonusCosts = (float) PlayerBonus::where('user_id', $userId)
                    ->where('status', 'completed')
                    ->sum('bonus_awarded_usd');

                // NGR Formula: max(0.00, (GGR - Bonus Cost) * 0.85) (85% after 15% platform fee)
                $ngrNetBase = $ggr - $bonusCosts;
                $ngr = max(0.00, $ngrNetBase * 0.85);

                $commission = round($ngr * ($revsharePct / 100.0), 2);

                $referral->update([
                    'total_bets_usd' => $totalBets,
                    'total_wins_usd' => $totalWins,
                    'net_ggr_usd' => $ggr,
                    'bonus_deductions_usd' => $bonusCosts,
                    'net_ngr_usd' => $ngr,
                    'commission_generated_usd' => $commission,
                ]);

                $totalGgr += $ggr;
                $totalNgr += $ngr;
                $totalCommission += $commission;
            }

            // Update affiliate profile balance (unpaid commissions)
            $unpaidCommission = max(0.00, $totalCommission - (float) $affiliate->lifetime_paid_usd);
            $affiliate->update([
                'commission_balance_usd' => $unpaidCommission,
                'lifetime_earned_usd' => $totalCommission,
            ]);

            return [
                'total_referrals' => $referrals->count(),
                'total_ggr_usd' => $totalGgr,
                'total_ngr_usd' => $totalNgr,
                'total_commission_usd' => $totalCommission,
                'commission_balance_usd' => $unpaidCommission,
            ];
        });
    }
}
