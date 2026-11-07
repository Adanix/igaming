@extends('layouts.admin')

@section('title', 'Withdrawal Queue & Payment Operations — NexusPlay Backoffice')

@section('content')
<div class="space-y-6" x-data="{
    rejectModal: false,
    selectedTx: null,
    rejectReason: '',
    openReject(tx) {
        this.selectedTx = tx;
        this.rejectReason = 'Compliance documentation incomplete.';
        this.rejectModal = true;
    }
}">

    <!-- Header & Stats Summary -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white">Withdrawal Approval &amp; Payment Operations</h1>
            <p class="text-xs text-slate-400">Multi-bucket cashier review queue with strict high-value &ge; $1,000 threshold enforcement</p>
        </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-amber-500/30 space-y-1">
            <span class="text-[10px] uppercase font-bold text-amber-400 tracking-wider">Pending Requests</span>
            <div class="text-xl font-mono font-black text-white">{{ $stats['pending_count'] }}</div>
            <p class="text-[11px] text-amber-300 font-mono">{{ format_usd($stats['pending_sum']) }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-rose-500/30 space-y-1">
            <span class="text-[10px] uppercase font-bold text-rose-400 tracking-wider">High-Value (&ge; $1,000)</span>
            <div class="text-xl font-mono font-black text-rose-400">{{ $stats['high_value_count'] }}</div>
            <p class="text-[11px] text-slate-400">Mandatory Ops Staff Review</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-emerald-500/30 space-y-1">
            <span class="text-[10px] uppercase font-bold text-emerald-400 tracking-wider">Completed Payouts</span>
            <div class="text-xl font-mono font-black text-emerald-400">{{ format_usd($stats['completed_sum']) }}</div>
            <p class="text-[11px] text-slate-400">Total Settled Outflows</p>
        </div>
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 space-y-1">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Current Filter</span>
            <div class="text-xl font-mono font-black text-slate-200 capitalize">{{ $statusFilter }}</div>
            <p class="text-[11px] text-slate-400">{{ $withdrawals->total() }} records</p>
        </div>
    </div>

    <!-- Filter Buttons -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
        <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="px-3.5 py-1.5 rounded-xl font-semibold transition {{ $statusFilter === 'pending' && !$highValueOnly ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
            Pending Queue
        </a>
        <a href="{{ route('admin.payments.index', ['status' => 'pending', 'high_value' => 1]) }}" class="px-3.5 py-1.5 rounded-xl font-semibold transition {{ $highValueOnly ? 'bg-rose-600 text-white font-bold' : 'bg-slate-900 text-rose-400 hover:text-white border border-slate-800' }}">
            High-Value Only (&ge; $1,000)
        </a>
        <a href="{{ route('admin.payments.index', ['status' => 'completed']) }}" class="px-3.5 py-1.5 rounded-xl font-semibold transition {{ $statusFilter === 'completed' ? 'bg-emerald-500 text-slate-950 font-bold' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
            Completed
        </a>
        <a href="{{ route('admin.payments.index', ['status' => 'all']) }}" class="px-3.5 py-1.5 rounded-xl font-semibold transition {{ $statusFilter === 'all' ? 'bg-slate-800 text-white font-bold' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
            All Records
        </a>
    </div>

    <!-- Queue Table -->
    <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
        <table class="w-full text-left text-xs text-slate-300">
            <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800 font-sans">
                <tr>
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Player</th>
                    <th class="px-4 py-3">Method &amp; Target</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Flags</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Requested</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 font-mono">
                @forelse($withdrawals as $w)
                    <tr class="{{ $w->is_high_value && $w->status === 'pending' ? 'bg-rose-950/10' : '' }}">
                        <td class="px-4 py-3 text-white font-semibold">{{ $w->reference }}</td>
                        <td class="px-4 py-3 font-sans">
                            <a href="{{ route('admin.players.show', $w->user_id) }}" class="font-bold text-white hover:text-amber-400">
                                {{ $w->user?->name ?? 'Player #' . $w->user_id }}
                            </a>
                            <span class="block text-[10px] text-slate-400 font-mono">{{ $w->user?->email }}</span>
                        </td>
                        <td class="px-4 py-3 font-sans">
                            <span class="text-slate-300">{{ $w->paymentMethod?->name ?? 'Payout Rail' }}</span>
                            @if(isset($w->metadata_json['account_details']))
                                <span class="block text-[10px] text-slate-500 font-mono truncate max-w-xs">{{ $w->metadata_json['account_details'] }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-black text-sm {{ $w->is_high_value ? 'text-amber-300' : 'text-slate-200' }}">
                            {{ format_usd($w->amount_usd) }}
                        </td>
                        <td class="px-4 py-3 font-sans">
                            @if($w->is_high_value)
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-black bg-rose-500/20 text-rose-300 border border-rose-500/40">
                                    High-Value (&ge; $1k)
                                </span>
                            @else
                                <span class="text-slate-500 text-[11px]">Standard</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-sans">
                            <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $w->status === 'completed' ? 'bg-emerald-500/20 text-emerald-400' : ($w->status === 'pending' ? 'bg-amber-500/20 text-amber-300' : 'bg-rose-500/20 text-rose-400') }}">
                                {{ $w->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-400 font-sans text-[11px]">{{ $w->created_at->format('M d, H:i') }}</td>
                        <td class="px-4 py-3 text-right font-sans">
                            @if($w->status === 'pending')
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.payments.approve', $w->id) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500 text-slate-950 font-bold text-[11px] hover:bg-emerald-400 transition shadow-sm">
                                            Approve
                                        </button>
                                    </form>
                                    <button @click="openReject({{ json_encode($w) }})" type="button" class="px-2.5 py-1.5 rounded-lg bg-rose-500/20 text-rose-300 hover:bg-rose-500/30 text-[11px] font-semibold border border-rose-500/40">
                                        Reject
                                    </button>
                                </div>
                            @else
                                <span class="text-slate-500 text-[11px]">Finalized</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-slate-500 font-sans">No withdrawal transactions in this filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div>{{ $withdrawals->links() }}</div>

    <!-- Reject Reason Modal -->
    <div x-show="rejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="rejectModal = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-slate-900 rounded-2xl border border-slate-800 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold">&times;</div>
                    <div>
                        <h3 class="text-base font-bold text-white">Reject Withdrawal Request</h3>
                        <p class="text-xs text-slate-400 font-mono" x-text="selectedTx ? selectedTx.reference : ''"></p>
                    </div>
                </div>

                <p class="text-xs text-slate-300 leading-relaxed">
                    Rejecting this withdrawal will immediately unlock and restore the frozen funds from <code class="text-slate-200">locked_withdrawal</code> back into the player's <code class="text-emerald-400">cash</code> balance bucket.
                </p>

                <form method="POST" :action="'/admin/payments/' + (selectedTx ? selectedTx.id : '') + '/reject'" class="space-y-4 text-xs">
                    @csrf
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-300">Rejection Justification</label>
                        <input type="text" name="reason" x-model="rejectReason" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                        <button @click="rejectModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white font-bold hover:bg-rose-500">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
