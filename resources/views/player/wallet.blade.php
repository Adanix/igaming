@extends('layouts.player')

@section('title', 'Cashier & USD Wallet — NexusPlay')

@section('content')
<div class="space-y-8" x-data="{ cashierTab: 'deposit' }">

    <!-- Header & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">USD Cashier &amp; Multi-Bucket Wallet</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Atomic USD balances backed by double-entry ledger entries. Deposit via simulated gateway or request withdrawals.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>Base Currency: USD ($)</span>
            </span>
        </div>
    </div>

    <!-- 3 Balance Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Cash Balance -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 border border-emerald-500/30 space-y-2 shadow-lg">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span class="font-bold uppercase tracking-wider text-emerald-400">Cash Balance</span>
                <span class="text-[10px] bg-emerald-500/10 text-emerald-300 px-2 py-0.5 rounded-full font-semibold">Withdrawable</span>
            </div>
            <div class="text-2xl sm:text-3xl font-mono font-black text-white">
                {{ format_usd($wallet->cash_balance_usd) }}
            </div>
            <p class="text-[11px] text-slate-400">Playable on all games. Instantly eligible for withdrawal.</p>
        </div>

        <!-- Bonus Balance -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 border border-amber-500/30 space-y-2 shadow-lg">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span class="font-bold uppercase tracking-wider text-amber-400">Bonus Balance</span>
                <span class="text-[10px] bg-amber-500/10 text-amber-300 px-2 py-0.5 rounded-full font-semibold">Playable Only</span>
            </div>
            <div class="text-2xl sm:text-3xl font-mono font-black text-amber-300">
                {{ format_usd($wallet->bonus_balance_usd) }}
            </div>
            <p class="text-[11px] text-slate-400">Converts to cash bucket upon completing wagering rollover.</p>
        </div>

        <!-- Locked Withdrawal -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 space-y-2 shadow-lg">
            <div class="flex items-center justify-between text-xs text-slate-400">
                <span class="font-bold uppercase tracking-wider text-slate-300">Locked In Withdrawal</span>
                <span class="text-[10px] bg-slate-800 text-slate-300 px-2 py-0.5 rounded-full font-semibold">Frozen</span>
            </div>
            <div class="text-2xl sm:text-3xl font-mono font-black text-slate-200">
                {{ format_usd($wallet->locked_withdrawal_usd) }}
            </div>
            <p class="text-[11px] text-slate-400">Pending ops approval or settlement. Automatically unlocked if rejected.</p>
        </div>
    </div>

    <!-- Cashier Tabs & Action Panel -->
    <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-6">
        <!-- Tabs -->
        <div class="flex items-center gap-3 border-b border-slate-800 pb-4">
            <button @click="cashierTab = 'deposit'" type="button"
                    :class="cashierTab === 'deposit' ? 'bg-emerald-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-300 hover:text-white'"
                    class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition">
                Deposit Funds
            </button>
            <button @click="cashierTab = 'withdraw'" type="button"
                    :class="cashierTab === 'withdraw' ? 'bg-emerald-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-300 hover:text-white'"
                    class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition">
                Withdraw Funds
            </button>
        </div>

        <!-- Deposit Form -->
        <div x-show="cashierTab === 'deposit'" class="space-y-6 max-w-2xl">
            <form method="POST" action="{{ route('wallet.deposit.store') }}" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Select Payment Rail</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($paymentMethods as $index => $method)
                            <label class="relative flex items-center gap-3 p-3.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-emerald-500/50 cursor-pointer transition">
                                <input type="radio" name="payment_method_id" value="{{ $method->id }}" {{ $index === 0 ? 'checked' : '' }} class="text-emerald-500 focus:ring-emerald-500 bg-slate-900 border-slate-700">
                                <div>
                                    <p class="text-xs font-bold text-white">{{ $method->name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ strtoupper($method->type) }} &bull; Fee: ${{ number_format($method->fee_fixed_usd, 2) }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Deposit Amount (USD)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-xs font-bold text-slate-400">$</span>
                        <input type="number" name="amount_usd" min="10" max="50000" step="5" value="100" required class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-8 pr-4 py-2.5 text-sm font-mono font-bold text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <p class="text-[11px] text-slate-400">Min: $10.00 &bull; Max: $50,000.00 USD</p>
                </div>

                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-400 text-slate-950 font-bold text-xs sm:text-sm hover:from-emerald-400 hover:to-emerald-300 shadow-md shadow-emerald-500/20 transition">
                    <span>Generate Invoice &amp; Open Sandbox &rarr;</span>
                </button>
            </form>
        </div>

        <!-- Withdrawal Form -->
        <div x-show="cashierTab === 'withdraw'" x-cloak class="space-y-6 max-w-2xl">
            <form method="POST" action="{{ route('wallet.withdraw.store') }}" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Select Payout Rail</label>
                    <select name="payment_method_id" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @foreach($paymentMethods as $method)
                            <option value="{{ $method->id }}">{{ $method->name }} ({{ strtoupper($method->type) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Withdrawal Amount (USD)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-xs font-bold text-slate-400">$</span>
                        <input type="number" name="amount_usd" min="10" step="5" max="{{ $wallet->cash_balance_usd }}" value="{{ min(50, (float)$wallet->cash_balance_usd) }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-8 pr-4 py-2.5 text-sm font-mono font-bold text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                        <span>Available Cash: <strong class="text-emerald-400 font-mono">{{ format_usd($wallet->cash_balance_usd) }}</strong></span>
                        <span class="text-amber-400">Threshold: &ge; $1,000.00 requires Ops Staff review</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Destination Crypto Address / Bank Details</label>
                    <input type="text" name="account_details" placeholder="e.g. TRC20 Address: TNx83jKs... or IBAN" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs font-mono text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 text-slate-950 font-bold text-xs sm:text-sm hover:from-amber-400 hover:to-amber-300 shadow-md shadow-amber-500/20 transition">
                    <span>Submit Withdrawal Request</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Recent Transactions Section (Anti-overflow card fallback) -->
    <div class="space-y-4">
        <h3 class="text-lg font-bold text-white">Recent Payment Transactions</h3>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Method</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 font-mono">
                    @forelse($transactions as $tx)
                        <tr>
                            <td class="px-4 py-3 text-white font-semibold">{{ $tx->reference }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $tx->direction === 'deposit' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-blue-500/20 text-blue-400' }}">
                                    {{ $tx->direction }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-300 font-sans">{{ $tx->paymentMethod?->name ?? 'Gateway' }}</td>
                            <td class="px-4 py-3 font-bold {{ $tx->direction === 'deposit' ? 'text-emerald-400' : 'text-slate-200' }}">
                                {{ format_usd($tx->amount_usd) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $tx->status === 'paid' || $tx->status === 'completed' ? 'bg-emerald-500/20 text-emerald-400' : ($tx->status === 'pending' ? 'bg-amber-500/20 text-amber-300' : 'bg-rose-500/20 text-rose-400') }}">
                                    {{ $tx->status }}
                                    @if($tx->is_high_value)<span class="text-[9px] text-amber-300 ml-1 font-bold">[VIP]</span>@endif
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400 text-[11px] font-sans">{{ $tx->created_at->format('M d, H:i') }}</td>
                            <td class="px-4 py-3 text-right font-sans">
                                @if($tx->direction === 'deposit' && $tx->status === 'pending')
                                    <a href="{{ route('wallet.deposit.sandbox', $tx->id) }}" class="text-emerald-400 hover:underline text-[11px] font-semibold">Open Sandbox &rarr;</a>
                                @elseif($tx->direction === 'withdrawal' && $tx->status === 'pending')
                                    <form method="POST" action="{{ route('wallet.withdraw.cancel', $tx->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-rose-400 hover:underline text-[11px] font-semibold">Cancel</button>
                                    </form>
                                @else
                                    <span class="text-slate-500 text-[11px]">&ndash;</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500 font-sans">No cashier transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards Fallback (< 768px) -->
        <div class="md:hidden space-y-3">
            @forelse($transactions as $tx)
                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-mono font-bold text-white text-[11px]">{{ $tx->reference }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $tx->status === 'paid' || $tx->status === 'completed' ? 'bg-emerald-500/20 text-emerald-400' : ($tx->status === 'pending' ? 'bg-amber-500/20 text-amber-300' : 'bg-rose-500/20 text-rose-400') }}">
                            {{ $tx->status }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span>{{ ucfirst($tx->direction) }} &bull; {{ $tx->paymentMethod?->name }}</span>
                        <span class="font-mono font-bold text-sm text-white">{{ format_usd($tx->amount_usd) }}</span>
                    </div>
                    @if($tx->direction === 'deposit' && $tx->status === 'pending')
                        <a href="{{ route('wallet.deposit.sandbox', $tx->id) }}" class="block text-center py-1.5 px-3 rounded-lg bg-emerald-500/10 text-emerald-400 font-bold border border-emerald-500/30 text-xs">
                            Open Payment Sandbox &rarr;
                        </a>
                    @elseif($tx->direction === 'withdrawal' && $tx->status === 'pending')
                        <form method="POST" action="{{ route('wallet.withdraw.cancel', $tx->id) }}">
                            @csrf
                            <button type="submit" class="w-full text-center py-1.5 px-3 rounded-lg bg-rose-500/10 text-rose-400 font-bold border border-rose-500/30 text-xs">
                                Cancel Withdrawal
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-center py-6 text-slate-500 text-xs">No cashier transactions found.</p>
            @endforelse
        </div>
    </div>

    <!-- Live Ledger Entries Audit Table (Anti-overflow card fallback) -->
    <div class="space-y-4 pt-4 border-t border-slate-800">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-white">Immutable Ledger Entries</h3>
            <span class="text-xs text-slate-400 font-mono">Bucket-Level Double Entry Trail</span>
        </div>

        <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Bucket</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Balance After</th>
                        <th class="px-4 py-3">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 font-mono text-[11px]">
                    @forelse($ledgerEntries as $entry)
                        <tr>
                            <td class="px-4 py-3 text-slate-400">{{ $entry->entry_reference }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded uppercase font-bold text-[10px] {{ $entry->balance_bucket === 'cash' ? 'bg-emerald-500/20 text-emerald-400' : ($entry->balance_bucket === 'bonus' ? 'bg-amber-500/20 text-amber-300' : 'bg-slate-800 text-slate-300') }}">
                                    {{ $entry->balance_bucket }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-300 font-sans">{{ str_replace('_', ' ', $entry->entry_type) }}</td>
                            <td class="px-4 py-3 font-bold {{ $entry->amount_usd >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $entry->amount_usd >= 0 ? '+' : '' }}{{ format_usd($entry->amount_usd) }}
                            </td>
                            <td class="px-4 py-3 text-white">
                                {{ format_usd($entry->balance_bucket === 'cash' ? $entry->cash_balance_after_usd : ($entry->balance_bucket === 'bonus' ? $entry->bonus_balance_after_usd : $entry->locked_balance_after_usd)) }}
                            </td>
                            <td class="px-4 py-3 text-slate-400 font-sans text-xs truncate max-w-xs">{{ $entry->description_en }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500 font-sans">No ledger entries recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-2.5">
            @forelse($ledgerEntries as $entry)
                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-xs space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-mono text-[10px] text-slate-400">{{ $entry->entry_reference }}</span>
                        <span class="font-mono font-bold {{ $entry->amount_usd >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $entry->amount_usd >= 0 ? '+' : '' }}{{ format_usd($entry->amount_usd) }}
                        </span>
                    </div>
                    <p class="text-slate-300 text-[11px]">{{ $entry->description_en }}</p>
                </div>
            @empty
                <p class="text-center py-6 text-slate-500 text-xs">No ledger entries recorded yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
