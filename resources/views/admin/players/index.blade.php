@extends('layouts.admin')

@section('title', 'Player Management Roster — NexusPlay Backoffice')

@section('content')
<div class="space-y-6">

    <!-- Header & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white">Player Accounts Roster</h1>
            <p class="text-xs text-slate-400">Total Players: <strong class="text-white">{{ $players->total() }}</strong> registered accounts</p>
        </div>

        <form method="GET" action="{{ route('admin.players.index') }}" class="flex items-center gap-2 max-w-sm w-full">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search player name or email..." class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500">
            <button type="submit" class="px-3 py-2 rounded-xl bg-slate-800 text-slate-200 hover:text-white text-xs font-semibold shrink-0">Search</button>
        </form>
    </div>

    <!-- Players Table -->
    <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
        <table class="w-full text-left text-xs text-slate-300">
            <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                <tr>
                    <th class="px-4 py-3">Player</th>
                    <th class="px-4 py-3">VIP Tier</th>
                    <th class="px-4 py-3">Cash USD</th>
                    <th class="px-4 py-3">Bonus USD</th>
                    <th class="px-4 py-3">Locked USD</th>
                    <th class="px-4 py-3">KYC Status</th>
                    <th class="px-4 py-3">Account Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($players as $p)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-bold text-white">{{ $p->name }}</p>
                            <p class="text-[10px] text-slate-400 font-mono">{{ $p->email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300">
                                {{ $p->vipTier?->name ?? 'Bronze' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono font-bold text-emerald-400">{{ format_usd($p->wallet?->cash_balance_usd ?? 0) }}</td>
                        <td class="px-4 py-3 font-mono text-amber-300">{{ format_usd($p->wallet?->bonus_balance_usd ?? 0) }}</td>
                        <td class="px-4 py-3 font-mono text-slate-400">{{ format_usd($p->wallet?->locked_withdrawal_usd ?? 0) }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $p->isKycVerified() ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                                {{ $p->kyc_status ?? 'unverified' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $p->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                {{ $p->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.players.show', $p->id) }}" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-200 hover:text-white border border-slate-700 text-[11px] font-semibold transition">
                                Dossier &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-slate-500">No players found matching query.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-3">
        @forelse($players as $p)
            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-bold text-white">{{ $p->name }}</p>
                        <p class="text-[10px] text-slate-400 font-mono">{{ $p->email }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $p->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                        {{ $p->status }}
                    </span>
                </div>
                <div class="flex justify-between font-mono text-[11px] text-slate-300 pt-1">
                    <span>Cash: <strong class="text-emerald-400">{{ format_usd($p->wallet?->cash_balance_usd ?? 0) }}</strong></span>
                    <span>Bonus: <strong class="text-amber-300">{{ format_usd($p->wallet?->bonus_balance_usd ?? 0) }}</strong></span>
                </div>
                <div class="pt-2 flex justify-end">
                    <a href="{{ route('admin.players.show', $p->id) }}" class="px-3 py-1 rounded-lg bg-slate-800 text-slate-200 text-xs font-semibold">
                        View Dossier &rarr;
                    </a>
                </div>
            </div>
        @empty
            <p class="text-center py-6 text-slate-500 text-xs">No players found.</p>
        @endforelse
    </div>

    <!-- Pagination -->
    <div>
        {{ $players->links() }}
    </div>
</div>
@endsection
