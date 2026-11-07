@extends('layouts.affiliate')

@section('title', 'Affiliate Performance & NGR Dashboard — NexusPlay')

@section('content')
<div class="space-y-8" x-data="{ payoutModal: false, copied: false }">

    <!-- Header & Recalculate CTA -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Affiliate Partner CRM &amp; NGR Engine</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Real-time Net Gaming Revenue (NGR) aggregation, referral player cohort tracking, and automated revshare settlement.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <form method="POST" action="{{ route('affiliate.recalculate') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-400 text-slate-950 font-bold text-xs hover:from-emerald-400 transition shadow-lg shadow-emerald-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Recalculate NGR &amp; Commissions</span>
                </button>
            </form>
            <button @click="payoutModal = true" type="button" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-200 border border-slate-700 hover:bg-slate-700 text-xs font-semibold transition">
                Request Payout
            </button>
        </div>
    </div>

    <!-- 4 Key Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Commission Balance -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-950/40 via-slate-900 to-slate-900 border border-emerald-500/40 space-y-1 shadow-lg">
            <span class="text-[10px] uppercase font-bold text-emerald-400 tracking-wider">Unpaid Commission</span>
            <div class="text-2xl sm:text-3xl font-mono font-black text-white">
                {{ format_usd($affiliate->commission_balance_usd) }}
            </div>
            <p class="text-[11px] text-slate-400">Available for settlement to external crypto or fiat rails.</p>
        </div>

        <!-- RevShare Tier -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-1 shadow-lg">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Current RevShare Rate</span>
            <div class="text-2xl sm:text-3xl font-mono font-black text-emerald-400">
                {{ $affiliate->revshare_percentage }}%
            </div>
            <p class="text-[11px] text-slate-400">Tier 3 Tier Rate (based on monthly cohort NGR volume).</p>
        </div>

        <!-- Total Referrals -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-1 shadow-lg">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Active Referred Players</span>
            <div class="text-2xl sm:text-3xl font-mono font-black text-white">
                {{ $referrals->count() }}
            </div>
            <p class="text-[11px] text-slate-400">Tagged with affiliate cookie / referral tracking code.</p>
        </div>

        <!-- Lifetime Earned -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-1 shadow-lg">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Lifetime Gross Earned</span>
            <div class="text-2xl sm:text-3xl font-mono font-black text-slate-200">
                {{ format_usd($affiliate->lifetime_earned_usd) }}
            </div>
            <p class="text-[11px] text-slate-400">Paid out to date: {{ format_usd($affiliate->lifetime_paid_usd) }}</p>
        </div>
    </div>

    <!-- Referral Link Box & NGR Formula Callout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Referral Link (7 cols) -->
        <div class="lg:col-span-7 p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-white">Unique Partner Tracking Link</span>
                <span class="text-xs font-mono text-emerald-400 font-bold">Code: {{ $affiliate->referral_code }}</span>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" readonly value="{{ url('/?ref=' . $affiliate->referral_code) }}" id="refLinkInput" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs font-mono text-slate-300 focus:outline-none">
                <button @click="navigator.clipboard.writeText(document.getElementById('refLinkInput').value); copied = true; setTimeout(() => copied = false, 2000)" type="button" class="shrink-0 px-4 py-2.5 rounded-xl bg-emerald-500 text-slate-950 font-bold text-xs hover:bg-emerald-400 transition">
                    <span x-text="copied ? 'Copied!' : 'Copy Link'"></span>
                </button>
            </div>
            <p class="text-[11px] text-slate-400">
                Any visitor registering through this URL is permanently tagged in the database to your affiliate ID.
            </p>
        </div>

        <!-- Formula Explainer (5 cols) -->
        <div class="lg:col-span-5 p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-2 text-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">Transparent NGR Formula</span>
            <div class="p-2.5 rounded-xl bg-slate-950 font-mono text-[11px] text-emerald-300 border border-emerald-500/20">
                NGR = (GGR &minus; Bonus Costs) &times; 85%
            </div>
            <p class="text-slate-400 text-[11px] leading-relaxed">
                Platform deducts 15% aggregator/gateway fee, then applies your 35% revenue share to net revenue.
            </p>
        </div>
    </div>

    <!-- Referred Players Cohort Table (Anti-overflow card fallback) -->
    <div class="space-y-4">
        <h3 class="text-lg font-bold text-white">Referred Players Performance Cohort</h3>

        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
            <table class="w-full text-left text-xs text-slate-300 font-mono">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800 font-sans">
                    <tr>
                        <th class="px-4 py-3">Player</th>
                        <th class="px-4 py-3">Registered</th>
                        <th class="px-4 py-3">Total Bets</th>
                        <th class="px-4 py-3">Total Wins</th>
                        <th class="px-4 py-3">Gross GGR</th>
                        <th class="px-4 py-3">Bonuses</th>
                        <th class="px-4 py-3">Net NGR</th>
                        <th class="px-4 py-3 text-right">Commission (35%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($referrals as $ref)
                        <tr>
                            <td class="px-4 py-3 text-white font-sans font-semibold">
                                {{ $ref->referredUser?->name ?? 'Player #' . $ref->referred_user_id }}
                                <span class="block text-[10px] text-slate-400 font-mono">{{ $ref->referredUser?->email }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-400 font-sans text-[11px]">{{ $ref->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-3">{{ format_usd($ref->total_bets_usd) }}</td>
                            <td class="px-4 py-3">{{ format_usd($ref->total_wins_usd) }}</td>
                            <td class="px-4 py-3 font-bold {{ $ref->net_ggr_usd >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ format_usd($ref->net_ggr_usd) }}
                            </td>
                            <td class="px-4 py-3 text-amber-300">{{ format_usd($ref->bonus_deductions_usd) }}</td>
                            <td class="px-4 py-3 font-bold text-white">{{ format_usd($ref->net_ngr_usd) }}</td>
                            <td class="px-4 py-3 text-right font-black text-emerald-400">
                                {{ format_usd($ref->commission_generated_usd) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-6 text-center text-slate-500 font-sans">No referrals recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card Fallback -->
        <div class="md:hidden space-y-3">
            @forelse($referrals as $ref)
                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 text-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-white">{{ $ref->referredUser?->name ?? 'Player #' . $ref->referred_user_id }}</span>
                        <span class="font-mono font-bold text-emerald-400">{{ format_usd($ref->commission_generated_usd) }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-slate-400 font-mono text-[11px] pt-1">
                        <div>Bets: <strong class="text-white">{{ format_usd($ref->total_bets_usd) }}</strong></div>
                        <div>Wins: <strong class="text-white">{{ format_usd($ref->total_wins_usd) }}</strong></div>
                        <div>GGR: <strong class="text-emerald-400">{{ format_usd($ref->net_ggr_usd) }}</strong></div>
                        <div>NGR: <strong class="text-white">{{ format_usd($ref->net_ngr_usd) }}</strong></div>
                    </div>
                </div>
            @empty
                <p class="text-center py-6 text-slate-500 text-xs">No referrals recorded yet.</p>
            @endforelse
        </div>
    </div>

    <!-- Payout Request Modal (Alpine.js) -->
    <div x-show="payoutModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="payoutModal = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-slate-900 rounded-2xl border border-slate-800 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 space-y-4">
                <h3 class="text-lg font-bold text-white">Request Commission Payout</h3>
                <p class="text-xs text-slate-400">Available Balance: <strong class="text-emerald-400 font-mono">{{ format_usd($affiliate->commission_balance_usd) }}</strong></p>

                <form method="POST" action="{{ route('affiliate.payout') }}" class="space-y-4 text-xs">
                    @csrf
                    <div class="space-y-1">
                        <label class="block font-bold text-slate-300">Payout Amount (USD)</label>
                        <input type="number" name="amount_usd" min="50" max="{{ $affiliate->commission_balance_usd }}" step="10" value="{{ min(100, (float)$affiliate->commission_balance_usd) }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono">
                    </div>

                    <div class="space-y-1">
                        <label class="block font-bold text-slate-300">Payout Method</label>
                        <select name="payout_method" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white">
                            <option value="usdt_trc20">USDT (TRC20)</option>
                            <option value="bitcoin">Bitcoin (BTC)</option>
                            <option value="bank_wire">Bank Wire Transfer</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block font-bold text-slate-300">Destination Address / Details</label>
                        <input type="text" name="payout_details" placeholder="e.g. TNx9... or IBAN" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                        <button @click="payoutModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-500 text-slate-950 font-bold hover:bg-emerald-400">Confirm Payout</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
