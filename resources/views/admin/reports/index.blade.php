@extends('layouts.admin')

@section('title', 'Gaming Analytics & Financial Reports — NexusPlay Backoffice')

@section('content')
<div class="space-y-8">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white">Gaming Analytics &amp; Revenue Reports</h1>
            <p class="text-xs text-slate-400">Aggregated performance per game title and game aggregator provider</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.reports.reconciliation') }}" class="px-3.5 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20 text-xs font-semibold transition">
                Ledger Invariant Audit &rarr;
            </a>
            <a href="{{ route('admin.reports.export') }}" class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-200 hover:text-white border border-slate-700 text-xs font-semibold transition">
                Export Ledger CSV
            </a>
        </div>
    </div>

    <!-- Games Performance Report -->
    <div class="space-y-4">
        <h3 class="text-base font-bold text-white">Title Performance Breakdown (GGR)</h3>

        <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
            <table class="w-full text-left text-xs text-slate-300 font-mono">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800 font-sans">
                    <tr>
                        <th class="px-4 py-3">Game Title</th>
                        <th class="px-4 py-3">Provider</th>
                        <th class="px-4 py-3">Settled Rounds</th>
                        <th class="px-4 py-3">Total Bets</th>
                        <th class="px-4 py-3">Total Wins</th>
                        <th class="px-4 py-3">Gross GGR</th>
                        <th class="px-4 py-3 text-right">House Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($gameReports as $g)
                        @php
                            $margin = $g->total_bets > 0 ? round(($g->ggr / $g->total_bets) * 100, 2) : 0.0;
                        @endphp
                        <tr>
                            <td class="px-4 py-3 font-sans font-bold text-white">{{ $g->title }}</td>
                            <td class="px-4 py-3 font-sans text-slate-400">{{ $g->provider?->name ?? 'Provider' }}</td>
                            <td class="px-4 py-3">{{ number_format($g->total_rounds) }}</td>
                            <td class="px-4 py-3">{{ format_usd($g->total_bets) }}</td>
                            <td class="px-4 py-3">{{ format_usd($g->total_wins) }}</td>
                            <td class="px-4 py-3 font-bold {{ $g->ggr >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ format_usd($g->ggr) }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-white">{{ $margin }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500 font-sans">No settled gaming telemetry recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Provider Breakdown -->
    <div class="space-y-4 pt-4 border-t border-slate-800">
        <h3 class="text-base font-bold text-white">Aggregator Providers Status</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($providerReports as $prov)
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-white text-sm">{{ $prov->name }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-500/20 text-emerald-400">
                            {{ $prov->status }}
                        </span>
                    </div>
                    <div class="flex justify-between text-slate-400 font-mono">
                        <span>Code: {{ $prov->code }}</span>
                        <span>Titles: <strong class="text-white">{{ $prov->games_count }}</strong></span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
