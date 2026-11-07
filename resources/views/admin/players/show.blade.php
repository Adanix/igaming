@extends('layouts.admin')

@section('title', 'Player Dossier: ' . $player->name . ' — NexusPlay Backoffice')

@section('content')
<div class="space-y-8 max-w-6xl">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.players.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-black text-white flex items-center gap-2">
                    <span>{{ $player->name }}</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-amber-500/20 text-amber-300">
                        {{ $player->vipTier?->name ?? 'Bronze' }}
                    </span>
                </h1>
                <p class="text-xs text-slate-400 font-mono">{{ $player->email }} &bull; Registered {{ $player->created_at->format('M d, Y') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 rounded-xl text-xs font-bold uppercase {{ $player->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                Status: {{ $player->status }}
            </span>
            <span class="px-2.5 py-1 rounded-xl text-xs font-bold uppercase {{ $player->isKycVerified() ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                KYC: {{ $player->kyc_status ?? 'unverified' }}
            </span>
        </div>
    </div>

    <!-- 3 Wallet Balance Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Cash Balance</span>
            <div class="text-2xl font-mono font-black text-emerald-400">
                {{ format_usd($player->wallet?->cash_balance_usd ?? 0) }}
            </div>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Bonus Balance</span>
            <div class="text-2xl font-mono font-black text-amber-300">
                {{ format_usd($player->wallet?->bonus_balance_usd ?? 0) }}
            </div>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Locked Withdrawal</span>
            <div class="text-2xl font-mono font-black text-slate-300">
                {{ format_usd($player->wallet?->locked_withdrawal_usd ?? 0) }}
            </div>
        </div>
    </div>

    <!-- Management Controls Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left: Status & KYC Update Panels (5 cols) -->
        <div class="lg:col-span-5 space-y-5">
            <!-- Account Status Form -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Account Lifecycle Status</h3>
                <form method="POST" action="{{ route('admin.players.status', $player->id) }}" class="flex items-center gap-2">
                    @csrf
                    <select name="status" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white">
                        <option value="active" {{ $player->status === 'active' ? 'selected' : '' }}>Active (Full Access)</option>
                        <option value="suspended" {{ $player->status === 'suspended' ? 'selected' : '' }}>Suspended (Login Blocked)</option>
                        <option value="excluded" {{ $player->status === 'excluded' ? 'selected' : '' }}>Self-Excluded (Mandatory Lock)</option>
                    </select>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-200 hover:text-white text-xs font-semibold shrink-0">
                        Update
                    </button>
                </form>
            </div>

            <!-- KYC Verification Form -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">KYC Verification State</h3>
                <form method="POST" action="{{ route('admin.players.kyc', $player->id) }}" class="flex items-center gap-2">
                    @csrf
                    <select name="kyc_status" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white">
                        <option value="unverified" {{ $player->kyc_status === 'unverified' ? 'selected' : '' }}>Unverified</option>
                        <option value="pending" {{ $player->kyc_status === 'pending' ? 'selected' : '' }}>Pending Review</option>
                        <option value="verified" {{ $player->kyc_status === 'verified' ? 'selected' : '' }}>Verified (Approved)</option>
                        <option value="rejected" {{ $player->kyc_status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-200 hover:text-white text-xs font-semibold shrink-0">
                        Update
                    </button>
                </form>
            </div>
        </div>

        <!-- Right: Manual Balance Adjustment with Audit Trail (7 cols) -->
        <div class="lg:col-span-7 p-6 rounded-2xl bg-slate-900 border border-amber-500/30 space-y-4">
            <div>
                <span class="text-[10px] uppercase font-bold text-amber-400 tracking-wider">Supervised Financial Mutation</span>
                <h3 class="text-base font-bold text-white mt-0.5">Manual USD Balance Adjustment</h3>
                <p class="text-xs text-slate-400">
                    Inserts an atomic double-entry ledger row and creates an immutable chained SHA-256 audit entry.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.players.adjust-balance', $player->id) }}" class="space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-300">Target Bucket</label>
                        <select name="balance_bucket" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white">
                            <option value="cash">Cash Bucket</option>
                            <option value="bonus">Bonus Bucket</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-300">Adjustment Amount USD (+ / -)</label>
                        <input type="number" step="0.50" name="amount_usd" placeholder="e.g. 50.00 or -25.00" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono">
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block font-bold text-slate-300">Mandatory Justification / Ticket Reference</label>
                    <input type="text" name="reason" placeholder="e.g. Compensation for aggregator timeout ticket #8492" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white">
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 transition shadow-md shadow-amber-500/20">
                        Execute Adjustment &amp; Sign Audit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Rounds for this Player -->
    <div class="space-y-3 pt-4 border-t border-slate-800">
        <h3 class="text-base font-bold text-white">Recent Settled Game Rounds</h3>
        <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
            <table class="w-full text-left text-xs text-slate-300 font-mono">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800 font-sans">
                    <tr>
                        <th class="px-4 py-3">Round Code</th>
                        <th class="px-4 py-3">Game</th>
                        <th class="px-4 py-3">Wager</th>
                        <th class="px-4 py-3">Payout</th>
                        <th class="px-4 py-3">Multiplier</th>
                        <th class="px-4 py-3">Outcome</th>
                        <th class="px-4 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($rounds as $rnd)
                        <tr>
                            <td class="px-4 py-3 text-slate-300">{{ $rnd->round_code }}</td>
                            <td class="px-4 py-3 text-white font-sans">{{ $rnd->game?->title ?? 'Game' }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ format_usd($rnd->bet_amount_usd) }}</td>
                            <td class="px-4 py-3 font-bold {{ $rnd->payout_amount_usd > 0 ? 'text-emerald-400' : 'text-slate-400' }}">{{ format_usd($rnd->payout_amount_usd) }}</td>
                            <td class="px-4 py-3">{{ $rnd->multiplier }}x</td>
                            <td class="px-4 py-3 uppercase font-bold text-[10px] {{ $rnd->outcome_type === 'big_win' ? 'text-amber-400' : ($rnd->payout_amount_usd > 0 ? 'text-emerald-400' : 'text-slate-400') }}">{{ $rnd->outcome_type }}</td>
                            <td class="px-4 py-3 text-slate-400 font-sans text-[11px]">{{ $rnd->created_at->format('M d, H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500 font-sans">No rounds played yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
