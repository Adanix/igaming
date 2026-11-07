@extends('layouts.admin')

@section('title', 'Executive Operations Dashboard — NexusPlay Backoffice')

@section('content')
<div class="space-y-8">

    <!-- Header & Live Ledger Status -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Platform Operations &amp; Finance Telemetry</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Authoritative real-time aggregation across gaming aggregator rounds, payment rails, and multi-bucket double-entry ledgers.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Reconciliation Badge -->
            <a href="{{ route('admin.reports.reconciliation') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl {{ $isReconciled ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' : 'bg-rose-500/10 border border-rose-500/30 text-rose-400' }} text-xs font-semibold">
                <span class="w-2.5 h-2.5 rounded-full {{ $isReconciled ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }}"></span>
                <span>Ledger Invariant: {{ $isReconciled ? '$0.00 Discrepancy (100% Reconciled)' : 'Discrepancy: ' . format_usd($discrepancy) }}</span>
            </a>
            <a href="{{ route('admin.reports.export') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 text-slate-200 border border-slate-800 hover:bg-slate-800 text-xs font-semibold transition">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- 4 Primary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Gross Gaming Revenue -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 space-y-1 shadow-lg">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span class="font-bold uppercase tracking-wider">Gross Gaming Revenue</span>
                <span class="text-[10px] bg-slate-800 px-2 py-0.5 rounded text-slate-300">GGR</span>
            </div>
            <div class="text-2xl sm:text-3xl font-mono font-black text-white">
                {{ format_usd($kpis['total_ggr']) }}
            </div>
            <div class="flex justify-between text-[11px] text-slate-400 pt-1">
                <span>Bets: {{ format_usd($kpis['total_bets']) }}</span>
                <span>Wins: {{ format_usd($kpis['total_wins']) }}</span>
            </div>
        </div>

        <!-- Net Gaming Revenue -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 border border-emerald-500/30 space-y-1 shadow-lg">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span class="font-bold uppercase tracking-wider text-emerald-400">Net Gaming Revenue</span>
                <span class="text-[10px] bg-emerald-500/20 text-emerald-300 px-2 py-0.5 rounded font-bold">NGR</span>
            </div>
            <div class="text-2xl sm:text-3xl font-mono font-black text-emerald-400">
                {{ format_usd($kpis['total_ngr']) }}
            </div>
            <p class="text-[11px] text-slate-400 pt-1">Net of 15% aggregator fee &amp; player promotions.</p>
        </div>

        <!-- Gross Deposits -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 space-y-1 shadow-lg">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span class="font-bold uppercase tracking-wider">Gross Inflow Deposits</span>
                <span class="text-[10px] bg-slate-800 px-2 py-0.5 rounded text-slate-300">USD</span>
            </div>
            <div class="text-2xl sm:text-3xl font-mono font-black text-white">
                {{ format_usd($kpis['gross_deposits']) }}
            </div>
            <p class="text-[11px] text-slate-400 pt-1">Total settled payment transactions.</p>
        </div>

        <!-- Pending Withdrawals -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 border border-amber-500/30 space-y-1 shadow-lg">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span class="font-bold uppercase tracking-wider text-amber-400">Pending Withdrawals</span>
                <span class="text-[10px] bg-amber-500/20 text-amber-300 px-2 py-0.5 rounded font-bold">{{ $kpis['pending_withdrawals_count'] }} Req</span>
            </div>
            <div class="text-2xl sm:text-3xl font-mono font-black text-amber-300">
                {{ format_usd($kpis['pending_withdrawals_sum']) }}
            </div>
            <p class="text-[11px] text-slate-400 pt-1">Locked in withdrawal bucket awaiting settlement.</p>
        </div>
    </div>

    <!-- Chart.js Trend & Pending Approvals Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- 7-Day Performance Chart (8 cols) -->
        <div class="lg:col-span-8 p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white">7-Day Gross Revenue &amp; Betting Volume</h3>
                    <p class="text-xs text-slate-400">Real-time daily telemetry from settled game rounds</p>
                </div>
            </div>

            <!-- Canvas wrapper (anti-overflow) -->
            <div class="w-full h-64 relative overflow-hidden">
                <canvas id="ggrChart"></canvas>
            </div>
        </div>

        <!-- Pending Withdrawal Queue Preview (4 cols) -->
        <div class="lg:col-span-4 p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 shadow-xl">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Withdrawal Queue</h3>
                <a href="{{ route('admin.payments.index') }}" class="text-xs text-amber-400 hover:underline">View All &rarr;</a>
            </div>

            <div class="space-y-3">
                @forelse($pendingWithdrawals as $pw)
                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-white">{{ $pw->user->name }}</span>
                            <span class="font-mono font-bold text-amber-300">{{ format_usd($pw->amount_usd) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span>{{ $pw->paymentMethod?->name ?? 'Wire' }}</span>
                            @if($pw->is_high_value)
                                <span class="px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 font-bold uppercase text-[9px]">&ge; $1,000 High-Value</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-1 border-t border-slate-800/80">
                            <form method="POST" action="{{ route('admin.payments.approve', $pw->id) }}">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-500 text-slate-950 font-bold text-[10px] hover:bg-emerald-400 transition">
                                    Approve
                                </button>
                            </form>
                            <a href="{{ route('admin.payments.index') }}" class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 text-[10px] hover:bg-slate-700">
                                Review
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-center py-8 text-slate-500 text-xs">No pending withdrawal requests in queue.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Chained Audit Trail Table -->
    <div class="space-y-4 pt-2 border-t border-slate-800">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-white">Tamper-Evident SHA-256 Chained Audit Trail</h3>
                <p class="text-xs text-slate-400">Cryptographically chained events with immutable parent integrity hashes</p>
            </div>
            <a href="{{ route('admin.audit.index') }}" class="text-xs text-emerald-400 hover:underline">Full Audit Log &rarr;</a>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
            <table class="w-full text-left text-xs text-slate-300 font-mono">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800 font-sans">
                    <tr>
                        <th class="px-4 py-3">Timestamp</th>
                        <th class="px-4 py-3">Actor</th>
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">Summary</th>
                        <th class="px-4 py-3">Integrity Hash</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($recentAudits as $audit)
                        <tr>
                            <td class="px-4 py-3 text-slate-400 font-sans text-[11px]">{{ $audit->created_at->format('M d, H:i:s') }}</td>
                            <td class="px-4 py-3 text-white font-sans font-semibold">{{ $audit->user?->name ?? 'System' }}</td>
                            <td class="px-4 py-3 text-amber-400 font-bold">{{ $audit->action }}</td>
                            <td class="px-4 py-3 text-slate-300 font-sans text-xs truncate max-w-xs">{{ $audit->summary }}</td>
                            <td class="px-4 py-3 text-emerald-400 text-[10px] font-mono">{{ substr($audit->integrity_hash, 0, 16) }}...</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500 font-sans">No audit records logged.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('ggrChart');
    if (!ctx) return;

    const chartData = {{ json_encode($chartData) }};

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Gross Gaming Revenue (GGR)',
                    data: chartData.ggr,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'Total Wagers Placed',
                    data: chartData.bets,
                    borderColor: '#38bdf8',
                    backgroundColor: 'transparent',
                    borderWidth: 1.5,
                    borderDash: [4, 4],
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#94a3b8', font: { size: 11 } }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(51, 65, 85, 0.3)' },
                    ticks: { color: '#94a3b8', font: { size: 10 } }
                },
                y: {
                    grid: { color: 'rgba(51, 65, 85, 0.3)' },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 10 },
                        callback: function (val) { return '$' + val; }
                    }
                }
            }
        }
    });
});
</script>
@endpush
