<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\WalletLedgerService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentManagementController extends Controller
{
    public function __construct(
        protected WalletLedgerService $ledgerService
    ) {}

    /**
     * Display the Withdrawal Approval Queue & Transactions.
     */
    public function index(Request $request): View
    {
        $statusFilter = $request->query('status', 'pending');
        $highValueOnly = $request->boolean('high_value');

        $query = PaymentTransaction::with('user', 'paymentMethod')
            ->where('type', 'withdrawal');

        if ($statusFilter === 'pending') {
            $query->whereIn('status', ['pending', 'pending_review']);
        } elseif ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($highValueOnly) {
            $query->where('is_high_value', true);
        }

        $withdrawals = $query->orderByDesc('is_high_value')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'pending_count' => PaymentTransaction::where('type', 'withdrawal')->whereIn('status', ['pending', 'pending_review'])->count(),
            'pending_sum' => (float) PaymentTransaction::where('type', 'withdrawal')->whereIn('status', ['pending', 'pending_review'])->sum('amount_usd'),
            'high_value_count' => PaymentTransaction::where('type', 'withdrawal')->whereIn('status', ['pending', 'pending_review'])->where('is_high_value', true)->count(),
            'completed_sum' => (float) PaymentTransaction::where('type', 'withdrawal')->whereIn('status', ['paid', 'completed'])->sum('amount_usd'),
        ];

        return view('admin.payments.index', compact('withdrawals', 'statusFilter', 'highValueOnly', 'stats'));
    }

    /**
     * Approve a pending withdrawal and finalize settlement.
     */
    public function approveWithdrawal(int $id, Request $request): RedirectResponse
    {
        $transaction = PaymentTransaction::with('user.wallet')
            ->where('id', $id)
            ->where('type', 'withdrawal')
            ->whereIn('status', ['pending', 'pending_review'])
            ->firstOrFail();

        $note = $request->input('note', 'Approved via backoffice operations panel.');

        try {
            $this->ledgerService->finalizeWithdrawal(
                $transaction->user->wallet,
                $transaction,
                $request->user(),
                $note
            );

            return redirect()->back()
                ->with('status', "Withdrawal {$transaction->reference} (" . format_usd($transaction->amount_usd) . ") approved and settled.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a pending withdrawal and unlock funds back to cash bucket.
     */
    public function rejectWithdrawal(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        $transaction = PaymentTransaction::with('user.wallet')
            ->where('id', $id)
            ->where('type', 'withdrawal')
            ->whereIn('status', ['pending', 'pending_review'])
            ->firstOrFail();

        try {
            $this->ledgerService->rejectWithdrawal(
                $transaction->user->wallet,
                $transaction,
                $request->user(),
                $validated['reason']
            );

            return redirect()->back()
                ->with('status', "Withdrawal {$transaction->reference} rejected. Locked funds returned to player cash balance.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
