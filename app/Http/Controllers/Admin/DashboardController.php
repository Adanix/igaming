<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\GameRound;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletLedgerEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Executive Operations Backoffice Dashboard.
     */
    public function index(): View
    {
        // 1. Core KPIs
        $roundsAgg = GameRound::where('status', 'settled')
            ->selectRaw('COALESCE(SUM(bet_amount_usd), 0) as total_bets, COALESCE(SUM(payout_amount_usd), 0) as total_wins')
            ->first();

        $totalBets = (float) ($roundsAgg->total_bets ?? 0);
        $totalWins = (float) ($roundsAgg->total_wins ?? 0);
        $totalGgr = $totalBets - $totalWins;
        $totalNgr = max(0.00, $totalGgr * 0.85);

        $grossDeposits = (float) PaymentTransaction::where('type', 'deposit')
            ->where('status', 'paid')
            ->sum('amount_usd');

        $pendingWithdrawalsQuery = PaymentTransaction::where('type', 'withdrawal')
            ->whereIn('status', ['pending', 'pending_review']);

        $pendingWithdrawalCount = $pendingWithdrawalsQuery->count();
        $pendingWithdrawalSum = (float) $pendingWithdrawalsQuery->sum('amount_usd');

        $totalPlayers = User::where('role', 'player')->count();

        // 2. Exact Reconciliation Invariant Check
        $walletCashSum = (float) Wallet::sum('cash_balance_usd');
        $ledgerCashSum = (float) WalletLedgerEntry::where('balance_bucket', 'cash')->sum('amount_usd');
        $discrepancy = round(abs($walletCashSum - $ledgerCashSum), 4);
        $isReconciled = $discrepancy === 0.0;

        // 3. 7-Day Trend for Chart.js
        $chartLabels = [];
        $chartGgr = [];
        $chartBets = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayLabel = now()->subDays($i)->format('M d');
            $chartLabels[] = $dayLabel;

            $dayRounds = GameRound::whereDate('created_at', $date)
                ->where('status', 'settled')
                ->selectRaw('COALESCE(SUM(bet_amount_usd), 0) as bets, COALESCE(SUM(payout_amount_usd), 0) as wins')
                ->first();

            $dayBets = (float) ($dayRounds->bets ?? 0);
            $dayWins = (float) ($dayRounds->wins ?? 0);
            $dayGgr = $dayBets - $dayWins;

            $chartBets[] = $dayBets;
            $chartGgr[] = $dayGgr;
        }

        // 4. Recent Audits & Pending Withdrawals
        $recentAudits = AuditLog::with('user')->latest('id')->limit(6)->get();
        $pendingWithdrawals = PaymentTransaction::with('user', 'paymentMethod')
            ->where('type', 'withdrawal')
            ->whereIn('status', ['pending', 'pending_review'])
            ->orderByDesc('is_high_value')
            ->latest('id')
            ->limit(5)
            ->get();

        $kpis = [
            'total_ggr' => $totalGgr,
            'total_ngr' => $totalNgr,
            'total_bets' => $totalBets,
            'total_wins' => $totalWins,
            'gross_deposits' => $grossDeposits,
            'pending_withdrawals_count' => $pendingWithdrawalCount,
            'pending_withdrawals_sum' => $pendingWithdrawalSum,
            'total_players' => $totalPlayers,
        ];

        $chartData = [
            'labels' => $chartLabels,
            'ggr' => $chartGgr,
            'bets' => $chartBets,
        ];

        return view('admin.dashboard', compact(
            'kpis',
            'isReconciled',
            'discrepancy',
            'chartData',
            'recentAudits',
            'pendingWithdrawals'
        ));
    }
}
