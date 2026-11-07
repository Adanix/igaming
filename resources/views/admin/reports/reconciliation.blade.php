@extends('layouts.admin')

@section('title', 'System Ledger Invariant Audit — NexusPlay Backoffice')

@section('content')
<div class="space-y-8">

    <!-- Header & Status -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white">System Mathematical Ledger Invariant Audit</h1>
            <p class="text-xs text-slate-400">Strict verification of double-entry bucket invariants across all user accounts (Section 28.1)</p>
        </div>

        <div>
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl {{ $systemSummary['is_fully_reconciled'] ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' : 'bg-rose-500/10 border border-rose-500/30 text-rose-400' }} text-xs font-bold">
                <span class="w-2.5 h-2.5 rounded-full {{ $systemSummary['is_fully_reconciled'] ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }}"></span>
                <span>{{ $systemSummary['is_fully_reconciled'] ? '100% MATHEMATICALLY RECONCILED ($0.00 DISCREPANCY)' : 'DISCREPANCY DETECTED' }}</span>
            </span>
        </div>
    </div>

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Users Audited</span>
            <div class="text-2xl font-mono font-black text-white">{{ $systemSummary['total_users_audited'] }}</div>
            <p class="text-[11px] text-slate-400">All registered wallets verified</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Total Wallets Cash</span>
            <div class="text-2xl font-mono font-black text-emerald-400">{{ format_usd($systemSummary['total_cash_wallets']) }}</div>
            <p class="text-[11px] text-slate-400">Sum of active wallet rows</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Total Ledger Cash Sum</span>
            <div class="text-2xl font-mono font-black text-emerald-400">{{ format_usd($systemSummary['total_cash_ledgers']) }}</div>
            <p class="text-[11px] text-slate-400">Sum of cash bucket ledger rows</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Aggregate Discrepancy</span>
            <div class="text-2xl font-mono font-black {{ $systemSummary['is_fully_reconciled'] ? 'text-emerald-400' : 'text-rose-400' }}">
                {{ format_usd($systemSummary['total_discrepancy']) }}
            </div>
            <p class="text-[11px] text-slate-400">Exact zero difference</p>
        </div>
    </div>

    <!-- Invariant Formula Callout -->
    <div class="p-5 rounded-2xl bg-slate-900 border border-emerald-500/30 space-y-2 text-xs">
        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Authoritative Ledger Invariants Enforced</span>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 font-mono text-[11px]">
            <div class="p-2.5 rounded-xl bg-slate-950 text-emerald-300 border border-slate-800">
                wallets.cash_balance_usd == SUM(ledger WHERE bucket = 'cash')
            </div>
            <div class="p-2.5 rounded-xl bg-slate-950 text-emerald-300 border border-slate-800">
                wallets.locked_withdrawal_usd == SUM(ledger WHERE bucket = 'locked_withdrawal')
            </div>
        </div>
    </div>

    <!-- Per-User Invariant Audit Breakdown Table -->
    <div class="space-y-4">
        <h3 class="text-base font-bold text-white">Per-Account Ledger Audit Roster</h3>

        <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
            <table class="w-full text-left text-xs text-slate-300 font-mono">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800 font-sans">
                    <tr>
                        <th class="px-4 py-3">Player Account</th>
                        <th class="px-4 py-3">Wallet Cash</th>
                        <th class="px-4 py-3">Ledger Cash Sum</th>
                        <th class="px-4 py-3">Cash Diff</th>
                        <th class="px-4 py-3">Wallet Locked</th>
                        <th class="px-4 py-3">Ledger Locked Sum</th>
                        <th class="px-4 py-3 text-right">Invariant State</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($auditResults as $row)
                        <tr>
                            <td class="px-4 py-3 font-sans">
                                <span class="font-bold text-white">{{ $row['user']->name }}</span>
                                <span class="block text-[10px] text-slate-400 font-mono">{{ $row['user']->email }}</span>
                            </td>
                            <td class="px-4 py-3 text-white">{{ format_usd($row['cash_wallet']) }}</td>
                            <td class="px-4 py-3 text-white">{{ format_usd($row['cash_ledger']) }}</td>
                            <td class="px-4 py-3 font-bold {{ $row['cash_diff'] === 0.0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ format_usd($row['cash_diff']) }}
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ format_usd($row['locked_wallet']) }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ format_usd($row['locked_ledger']) }}</td>
                            <td class="px-4 py-3 text-right font-sans">
                                @if($row['is_clean'])
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        &check; Reconciled
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                        &times; Mismatch
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
