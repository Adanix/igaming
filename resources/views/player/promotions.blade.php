@extends('layouts.player')

@section('title', 'Bonuses & Promotions — NexusPlay')

@section('content')
<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Promotions &amp; Bonus Rollover Engine</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Transparent rollover tracking, automatic cash conversion upon wagering completion, and zero hidden clauses.
            </p>
        </div>
    </div>

    <!-- Active Bonus Card (If attached) -->
    @if($activeBonus)
        <div class="p-6 rounded-3xl bg-gradient-to-br from-amber-950/40 via-slate-900 to-slate-900 border-2 border-amber-500/40 space-y-5 shadow-2xl">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold">
                        🎁
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-amber-400 tracking-wider">Active Bonus In Progress</span>
                        <h2 class="text-lg font-bold text-white">{{ $activeBonus->promotion->title_en }}</h2>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('promotions.forfeit', $activeBonus->id) }}" onsubmit="return confirm('Forfeiting this bonus will wipe the remaining bonus balance. Confirm?')">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-800 text-rose-300 border border-rose-500/30 hover:bg-rose-500/10 text-xs font-semibold transition">
                            Forfeit Bonus
                        </button>
                    </form>
                </div>
            </div>

            <!-- Rollover Progress Bar -->
            <div class="space-y-2">
                <div class="flex justify-between text-xs">
                    <span class="text-slate-300 font-semibold">Wagering Progress</span>
                    <span class="font-mono font-bold text-amber-300">{{ number_format($activeBonus->progressPercentage(), 1) }}% Completed</span>
                </div>
                <div class="w-full bg-slate-950 rounded-full h-3.5 p-0.5 border border-amber-500/30">
                    <div class="bg-gradient-to-r from-amber-500 to-amber-300 h-2.5 rounded-full transition-all duration-300" style="width: {{ $activeBonus->progressPercentage() }}%"></div>
                </div>
                <div class="flex justify-between text-[11px] font-mono text-slate-400 pt-1">
                    <span>Wagered: <strong class="text-white">{{ format_usd($activeBonus->wagering_completed_usd) }}</strong></span>
                    <span>Required: <strong class="text-white">{{ format_usd($activeBonus->wagering_required_usd) }}</strong></span>
                    <span>Bonus Awarded: <strong class="text-amber-300">{{ format_usd($activeBonus->bonus_awarded_usd) }}</strong></span>
                </div>
            </div>

            <p class="text-[11px] text-slate-400 leading-relaxed">
                Wagers placed on slots contribute 100% towards the requirement. Once progress hits 100%, any remaining bonus balance automatically converts to withdrawable USD cash.
            </p>
        </div>
    @endif

    <!-- Available Promotions Grid -->
    <div class="space-y-4">
        <h3 class="text-lg font-bold text-white">Available Campaign Offers</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($promotions as $promo)
                <div class="flex flex-col justify-between rounded-2xl bg-slate-900 border border-slate-800 p-5 space-y-4 shadow-lg hover:border-slate-700 transition">
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] uppercase font-bold tracking-wider bg-emerald-500/20 text-emerald-400">
                                {{ str_replace('_', ' ', $promo->promo_type) }}
                            </span>
                            <span class="text-xs font-mono text-slate-400">Code: <strong class="text-white">{{ $promo->code }}</strong></span>
                        </div>

                        <h4 class="text-base font-bold text-white">{{ $promo->title_en }}</h4>
                        <p class="text-xs text-slate-400 leading-relaxed">{{ $promo->description_en }}</p>

                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs space-y-1">
                            <div class="flex justify-between text-slate-400">
                                <span>Max Bonus:</span>
                                <span class="font-mono font-bold text-emerald-400">{{ format_usd($promo->max_bonus_usd) }}</span>
                            </div>
                            <div class="flex justify-between text-slate-400">
                                <span>Wagering Multiplier:</span>
                                <span class="font-mono font-bold text-white">{{ $promo->wagering_multiplier }}x</span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('promotions.claim', $promo->id) }}" class="pt-2">
                        @csrf
                        <button type="submit" {{ $activeBonus ? 'disabled' : '' }} class="w-full py-2.5 px-4 rounded-xl font-bold text-xs transition {{ $activeBonus ? 'bg-slate-800 text-slate-500 cursor-not-allowed' : 'bg-gradient-to-r from-emerald-500 to-emerald-400 text-slate-950 hover:from-emerald-400 hover:to-emerald-300 shadow-md shadow-emerald-500/20' }}">
                            {{ $activeBonus ? 'Active Bonus In Progress' : 'Claim Offer' }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Bonus History Section -->
    @if($bonusHistory->isNotEmpty())
        <div class="space-y-4 pt-6 border-t border-slate-800">
            <h3 class="text-base font-bold text-white">Bonus Campaign History</h3>
            <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
                <table class="w-full text-left text-xs text-slate-300 font-mono">
                    <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="px-4 py-3">Campaign</th>
                            <th class="px-4 py-3">Bonus Awarded</th>
                            <th class="px-4 py-3">Required</th>
                            <th class="px-4 py-3">Completed</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @foreach($bonusHistory as $item)
                            <tr>
                                <td class="px-4 py-3 text-white font-sans">{{ $item->promotion->title_en }}</td>
                                <td class="px-4 py-3 text-amber-300 font-bold">{{ format_usd($item->bonus_awarded_usd) }}</td>
                                <td class="px-4 py-3">{{ format_usd($item->wagering_required_usd) }}</td>
                                <td class="px-4 py-3">{{ format_usd($item->wagering_completed_usd) }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $item->status === 'completed' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-[11px] font-sans">{{ $item->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
