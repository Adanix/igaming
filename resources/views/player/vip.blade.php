@extends('layouts.player')

@section('title', 'VIP Club & Rewards — NexusPlay')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">VIP Loyalty Club &amp; Tier Status</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Wager on games to accumulate lifetime VIP points, unlock higher cashback percentages, and access dedicated concierge services.
            </p>
        </div>
    </div>

    <!-- Current Tier & Cashback Claim Hero -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-stretch">

        <!-- Left: Current Tier Status Card (7 cols) -->
        <div class="md:col-span-7 p-6 rounded-3xl bg-gradient-to-br from-purple-950/30 via-slate-900 to-slate-900 border border-purple-500/30 space-y-5 shadow-2xl flex flex-col justify-between">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] uppercase font-bold text-purple-400 tracking-wider">Your Current Loyalty Tier</span>
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-purple-500/20 text-purple-300 border border-purple-500/30">
                        {{ $currentTier->name }} Club
                    </span>
                </div>

                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-600 to-amber-500 flex items-center justify-center text-slate-950 text-2xl font-black shadow-lg">
                        👑
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-white">{{ $currentTier->name }} Member</h2>
                        <p class="text-xs text-slate-400">Lifetime Wagered: <strong class="text-white font-mono">{{ format_usd($user->vip_points) }}</strong></p>
                    </div>
                </div>

                <!-- Progress to next tier -->
                @if($nextTier)
                    <div class="space-y-2 pt-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-300 font-semibold">Progress to {{ $nextTier->name }}</span>
                            <span class="font-mono font-bold text-purple-300">{{ $progressPct }}%</span>
                        </div>
                        <div class="w-full bg-slate-950 rounded-full h-3 p-0.5 border border-purple-500/30">
                            <div class="bg-gradient-to-r from-purple-500 to-amber-400 h-2 rounded-full" style="width: {{ $progressPct }}%"></div>
                        </div>
                        <div class="flex justify-between text-[11px] font-mono text-slate-400">
                            <span>Target: {{ format_usd($nextTier->min_wagered_usd) }}</span>
                            <span>Remaining: {{ format_usd(max(0, (float)$nextTier->min_wagered_usd - (float)$user->vip_points)) }}</span>
                        </div>
                    </div>
                @else
                    <div class="p-3 rounded-xl bg-purple-500/10 border border-purple-500/30 text-xs text-purple-300 font-bold">
                        &star; You have achieved the highest tier (Platinum). Enjoy maximum cashback and bespoke VIP hosting!
                    </div>
                @endif
            </div>

            <div class="pt-2 text-xs text-slate-400">
                Cashback Rate: <strong class="text-emerald-400 font-mono">{{ $currentTier->cashback_percentage }}%</strong> on settled losses.
            </div>
        </div>

        <!-- Right: Cashback Claim Box (5 cols) -->
        <div class="md:col-span-5 p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-4 shadow-xl flex flex-col justify-between">
            <div class="space-y-2">
                <span class="text-[10px] uppercase font-bold text-emerald-400 tracking-wider">Weekly VIP Cashback</span>
                <h3 class="text-lg font-bold text-white">Instant Loss Recovery</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    VIP cashback is calculated dynamically from your settled round activity and credited straight into your cash wallet with zero wagering attachments.
                </p>

                <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-1 text-center my-3">
                    <span class="text-[10px] text-slate-400 uppercase font-semibold">Eligible Cashback Available</span>
                    <div class="text-3xl font-black font-mono {{ $hasClaimedThisWeek ? 'text-slate-500' : 'text-emerald-400' }}">
                        {{ format_usd($eligibleCashback) }}
                    </div>
                    @if($hasClaimedThisWeek)
                        <span class="text-[10px] text-emerald-400 font-semibold">&check; Already claimed for Week {{ now()->format('W') }}</span>
                    @else
                        <span class="text-[10px] text-slate-500">Credited directly to cash balance</span>
                    @endif
                </div>
            </div>

            @if($hasClaimedThisWeek)
                <button type="button" disabled class="w-full py-3 px-4 rounded-xl bg-slate-800/80 text-slate-500 font-bold text-xs sm:text-sm cursor-not-allowed border border-slate-700/50">
                    &check; Cashback Already Claimed This Week
                </button>
            @else
                <form method="POST" action="{{ route('vip.claim-cashback') }}">
                    @csrf
                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-400 text-slate-950 font-bold text-xs sm:text-sm hover:from-emerald-400 transition shadow-lg shadow-emerald-500/20">
                        Claim VIP Cashback Reward
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- VIP Tier Matrix Table -->
    <div class="space-y-4 pt-4 border-t border-slate-800">
        <h3 class="text-lg font-bold text-white">VIP Tier Privileges &amp; Matrix</h3>

        <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Tier</th>
                        <th class="px-4 py-3">Qualifying Wager</th>
                        <th class="px-4 py-3">Weekly Cashback</th>
                        <th class="px-4 py-3">Withdrawal Speed</th>
                        <th class="px-4 py-3">Dedicated Host</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($vipTiers as $tier)
                        @php $isCurrent = $tier->id === $currentTier->id; @endphp
                        <tr class="{{ $isCurrent ? 'bg-purple-950/20' : '' }}">
                            <td class="px-4 py-3 font-bold text-white flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full {{ $tier->name === 'Platinum' ? 'bg-purple-400' : ($tier->name === 'Gold' ? 'bg-amber-400' : ($tier->name === 'Silver' ? 'bg-slate-300' : 'bg-amber-700')) }}"></span>
                                <span>{{ $tier->name }}</span>
                                @if($isCurrent)
                                    <span class="px-2 py-0.5 rounded-full text-[9px] bg-purple-500/30 text-purple-300 font-bold">CURRENT</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-mono text-slate-300">${{ number_format($tier->min_wagered_usd, 0) }} USD</td>
                            <td class="px-4 py-3 font-mono font-bold text-emerald-400">{{ $tier->cashback_percentage }}%</td>
                            <td class="px-4 py-3 text-slate-300">
                                {{ $tier->name === 'Platinum' ? 'Instant Priority' : ($tier->name === 'Gold' ? 'Express (< 1h)' : 'Standard') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($tier->min_wagered_usd >= 10000)
                                    <span class="text-emerald-400 font-semibold">&check; 24/7 Dedicated Concierge</span>
                                @else
                                    <span class="text-slate-500">&times; Standard Support</span>
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
