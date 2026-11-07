<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameRound;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\WalletLedgerEntry;
use App\Services\AuditLoggerService;
use App\Services\WalletLedgerService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayerManagementController extends Controller
{
    public function __construct(
        protected WalletLedgerService $ledgerService,
        protected AuditLoggerService $auditLogger
    ) {}

    /**
     * Display Player Roster with filters.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $status = $request->query('status');
        $kyc = $request->query('kyc');

        $query = User::with('wallet', 'vipTier')
            ->where('role', 'player');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($kyc) {
            $query->where('kyc_status', $kyc);
        }

        $players = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.players.index', compact('players', 'search', 'status', 'kyc'));
    }

    /**
     * Detailed Player Profile Dossier.
     */
    public function show(int $id): View
    {
        $player = User::with('wallet', 'vipTier', 'activeBonus.promotion')->findOrFail($id);

        $rounds = GameRound::with('game')
            ->where('user_id', $player->id)
            ->latest('id')
            ->limit(10)
            ->get();

        $ledgerEntries = WalletLedgerEntry::where('user_id', $player->id)
            ->latest('id')
            ->limit(15)
            ->get();

        $transactions = PaymentTransaction::with('paymentMethod')
            ->where('user_id', $player->id)
            ->latest('id')
            ->limit(10)
            ->get();

        return view('admin.players.show', compact('player', 'rounds', 'ledgerEntries', 'transactions'));
    }

    /**
     * Toggle player account status.
     */
    public function updateStatus(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,suspended,excluded'],
        ]);

        $player = User::findOrFail($id);
        $oldStatus = $player->status;
        $player->status = $validated['status'];
        $player->save();

        $this->auditLogger->log(
            'player.status_update',
            $request->user(),
            User::class,
            $player->id,
            "Updated player status from {$oldStatus} to {$player->status}"
        );

        return redirect()->back()
            ->with('status', "Player {$player->name} status changed to {$player->status}.");
    }

    /**
     * Update player KYC verification status.
     */
    public function updateKyc(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kyc_status' => ['required', 'in:unverified,pending,verified,rejected'],
        ]);

        $player = User::findOrFail($id);
        $oldKyc = $player->kyc_status;
        $player->kyc_status = $validated['kyc_status'];
        $player->save();

        $this->auditLogger->log(
            'player.kyc_update',
            $request->user(),
            User::class,
            $player->id,
            "Updated player KYC from {$oldKyc} to {$player->kyc_status}"
        );

        return redirect()->back()
            ->with('status', "Player {$player->name} KYC updated to {$player->kyc_status}.");
    }

    /**
     * Execute a manual USD wallet balance adjustment with chained audit trail.
     */
    public function adjustBalance(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'balance_bucket' => ['required', 'in:cash,bonus'],
            'amount_usd' => ['required', 'numeric'],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $player = User::with('wallet')->findOrFail($id);
        $wallet = $player->wallet;

        if (! $wallet) {
            return redirect()->back()->with('error', 'Player has no wallet configured.');
        }

        try {
            $amount = (float) $validated['amount_usd'];
            $bucket = $validated['balance_bucket'];
            $reason = $validated['reason'];
            $action = $amount >= 0 ? 'credit' : 'debit';

            $this->ledgerService->manualAdminAdjust($wallet, $bucket, $action, abs($amount), $reason, $request->user());

            return redirect()->back()
                ->with('status', "Wallet adjusted by " . format_usd($amount) . " in [{$bucket}] bucket. Ledger entry & audit log created.");
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
