<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\WalletLedgerEntry;
use App\Services\Simulators\SimulatedPaymentGatewayService;
use App\Services\WalletLedgerService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(
        protected WalletLedgerService $ledgerService,
        protected SimulatedPaymentGatewayService $gatewayService
    ) {}

    /**
     * Display the Player Cashier overview (Balances, Deposit, Withdrawal, Ledger).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $wallet = $user->wallet;

        $paymentMethods = PaymentMethod::where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        $transactions = PaymentTransaction::with('paymentMethod')
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(15)
            ->get();

        $ledgerEntries = WalletLedgerEntry::where('user_id', $user->id)
            ->latest('id')
            ->limit(20)
            ->get();

        return view('player.wallet', compact('wallet', 'paymentMethods', 'transactions', 'ledgerEntries'));
    }

    /**
     * Initiate a simulated deposit invoice.
     */
    public function storeDeposit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'amount_usd' => ['required', 'numeric', 'min:10.00', 'max:50000.00'],
        ]);

        $user = $request->user();
        $method = PaymentMethod::findOrFail($validated['payment_method_id']);
        $amount = round((float) $validated['amount_usd'], 2);

        $reference = 'TX-DEP-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        $transaction = PaymentTransaction::create([
            'user_id' => $user->id,
            'wallet_id' => $user->wallet->id,
            'payment_method_id' => $method->id,
            'type' => 'deposit',
            'amount_usd' => $amount,
            'fee_usd' => 0.00,
            'net_amount_usd' => $amount,
            'currency' => 'USD',
            'status' => 'pending',
            'reference' => $reference,
            'idempotency_key' => 'idemp-dep-' . $reference,
            'destination_account' => $request->ip(),
        ]);

        return redirect()->route('wallet.deposit.sandbox', $transaction->id);
    }

    /**
     * Display the interactive Payment Gateway Sandbox for this invoice.
     */
    public function showSandbox(int $id, Request $request): View
    {
        $user = $request->user();
        $transaction = PaymentTransaction::with('paymentMethod')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $sandboxData = $this->gatewayService->buildPayload($transaction, 'paid');

        return view('player.deposit-sandbox', compact('transaction', 'sandboxData'));
    }

    /**
     * Submit a cash withdrawal request.
     */
    public function storeWithdrawal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'amount_usd' => ['required', 'numeric', 'min:10.00'],
            'account_details' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $wallet = $user->wallet;
        $amount = round((float) $validated['amount_usd'], 2);

        if ((float) $wallet->cash_balance_usd < $amount) {
            return redirect()->back()
                ->with('error', "Insufficient cash balance. Available: " . format_usd($wallet->cash_balance_usd));
        }

        $method = PaymentMethod::findOrFail($validated['payment_method_id']);
        $reference = 'TX-WTD-' . date('Ymd') . '-' . strtoupper(Str::random(6));
        $isHighValue = $amount >= 1000.00;

        try {
            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'payment_method_id' => $method->id,
                'type' => 'withdrawal',
                'amount_usd' => $amount,
                'fee_usd' => 0.00,
                'net_amount_usd' => $amount,
                'currency' => 'USD',
                'status' => $isHighValue ? 'pending_review' : 'pending',
                'reference' => $reference,
                'idempotency_key' => 'idemp-wtd-' . $reference,
                'is_high_value' => $isHighValue,
                'destination_account' => $validated['account_details'],
            ]);

            $this->ledgerService->lockForWithdrawal($wallet, $amount, $transaction);

            $msg = $isHighValue
                ? "Withdrawal request of " . format_usd($amount) . " submitted. High-value threshold reached; queued for Ops Staff review."
                : "Withdrawal request of " . format_usd($amount) . " submitted and funds locked in withdrawal bucket.";

            return redirect()->route('wallet.index')->with('status', $msg);
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a pending withdrawal before ops approval.
     */
    public function cancelWithdrawal(int $id, Request $request): RedirectResponse
    {
        $user = $request->user();
        $transaction = PaymentTransaction::where('id', $id)
            ->where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->whereIn('status', ['pending', 'pending_review'])
            ->firstOrFail();

        try {
            $this->ledgerService->cancelWithdrawal($user->wallet, $transaction);

            return redirect()->route('wallet.index')
                ->with('status', "Withdrawal {$transaction->reference} has been cancelled. Funds restored to cash balance.");
        } catch (Exception $e) {
            return redirect()->route('wallet.index')->with('error', $e->getMessage());
        }
    }
}
