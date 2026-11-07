<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameProvider;
use App\Models\GameRound;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletLedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display Financial & Gaming Analytics Reports.
     */
    public function index(): View
    {
        $gameReports = Game::with('provider')
            ->leftJoin('game_rounds', function ($join) {
                $join->on('games.id', '=', 'game_rounds.game_id')
                    ->where('game_rounds.status', '=', 'settled');
            })
            ->select(
                'games.id',
                'games.title',
                'games.game_code',
                'games.game_provider_id',
                DB::raw('COUNT(game_rounds.id) as total_rounds'),
                DB::raw('COALESCE(SUM(game_rounds.bet_amount_usd), 0) as total_bets'),
                DB::raw('COALESCE(SUM(game_rounds.payout_amount_usd), 0) as total_wins'),
                DB::raw('COALESCE(SUM(game_rounds.bet_amount_usd), 0) - COALESCE(SUM(game_rounds.payout_amount_usd), 0) as ggr')
            )
            ->groupBy('games.id', 'games.title', 'games.game_code', 'games.game_provider_id')
            ->orderByDesc('ggr')
            ->get();

        $providerReports = GameProvider::withCount('games')->get();

        return view('admin.reports.index', compact('gameReports', 'providerReports'));
    }

    /**
     * System-Wide Mathematical Ledger Invariant Audit (Section 28.1).
     */
    public function reconciliation(): View
    {
        $users = User::with('wallet')->whereHas('wallet')->get();
        $auditResults = [];

        $totalCashWallet = 0.00;
        $totalCashLedger = 0.00;
        $totalDiscrepancy = 0.00;

        foreach ($users as $user) {
            $wallet = $user->wallet;
            $cashWallet = (float) $wallet->cash_balance_usd;
            $bonusWallet = (float) $wallet->bonus_balance_usd;
            $lockedWallet = (float) $wallet->locked_withdrawal_usd;

            $cashLedger = (float) WalletLedgerEntry::where('wallet_id', $wallet->id)
                ->where('balance_bucket', 'cash')
                ->sum('amount_usd');

            $bonusLedger = (float) WalletLedgerEntry::where('wallet_id', $wallet->id)
                ->where('balance_bucket', 'bonus')
                ->sum('amount_usd');

            $lockedLedger = (float) WalletLedgerEntry::where('wallet_id', $wallet->id)
                ->where('balance_bucket', 'locked_withdrawal')
                ->sum('amount_usd');

            $cashDiff = round(abs($cashWallet - $cashLedger), 4);
            $bonusDiff = round(abs($bonusWallet - $bonusLedger), 4);
            $lockedDiff = round(abs($lockedWallet - $lockedLedger), 4);

            $isClean = ($cashDiff === 0.0) && ($bonusDiff === 0.0) && ($lockedDiff === 0.0);

            $auditResults[] = [
                'user' => $user,
                'wallet' => $wallet,
                'cash_wallet' => $cashWallet,
                'cash_ledger' => $cashLedger,
                'cash_diff' => $cashDiff,
                'bonus_wallet' => $bonusWallet,
                'bonus_ledger' => $bonusLedger,
                'locked_wallet' => $lockedWallet,
                'locked_ledger' => $lockedLedger,
                'is_clean' => $isClean,
            ];

            $totalCashWallet += $cashWallet;
            $totalCashLedger += $cashLedger;
            $totalDiscrepancy += ($cashDiff + $bonusDiff + $lockedDiff);
        }

        $systemSummary = [
            'total_users_audited' => count($auditResults),
            'total_cash_wallets' => $totalCashWallet,
            'total_cash_ledgers' => $totalCashLedger,
            'total_discrepancy' => $totalDiscrepancy,
            'is_fully_reconciled' => $totalDiscrepancy === 0.0,
        ];

        return view('admin.reports.reconciliation', compact('auditResults', 'systemSummary'));
    }

    /**
     * Export Streamed Financial Audit CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $filename = 'nexusplay-audit-ledger-' . date('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'ID',
                'Timestamp',
                'User ID',
                'User Name',
                'Entry Reference',
                'Entry Type',
                'Balance Bucket',
                'Amount USD',
                'Cash Before',
                'Cash After',
                'Bonus Before',
                'Bonus After',
                'Locked Before',
                'Locked After',
                'Description',
            ]);

            WalletLedgerEntry::with('user')
                ->orderByDesc('id')
                ->chunk(200, function ($entries) use ($handle) {
                    foreach ($entries as $entry) {
                        fputcsv($handle, [
                            $entry->id,
                            $entry->created_at->toIso8601String(),
                            $entry->user_id,
                            $entry->user?->name ?? 'System',
                            $entry->entry_reference,
                            $entry->entry_type,
                            $entry->balance_bucket,
                            $entry->amount_usd,
                            $entry->cash_balance_before_usd,
                            $entry->cash_balance_after_usd,
                            $entry->bonus_balance_before_usd,
                            $entry->bonus_balance_after_usd,
                            $entry->locked_balance_before_usd,
                            $entry->locked_balance_after_usd,
                            $entry->description_en,
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
    }
}
