@extends('layouts.admin')

@section('title', 'Campaigns & Bonus Rollover Configurations — NexusPlay Backoffice')

@section('content')
<div class="space-y-6" x-data="{ newPromoModal: false }">

    <!-- Header & Create Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white">Promotions &amp; Bonus Campaigns</h1>
            <p class="text-xs text-slate-400">Configure deposit matches, free chips, and wagering multiplier requirements</p>
        </div>

        <button @click="newPromoModal = true" type="button" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-bold text-xs hover:from-amber-400 transition shadow-md shadow-amber-500/20">
            <span>+ Create Campaign</span>
        </button>
    </div>

    <!-- Promotions Table -->
    <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
        <table class="w-full text-left text-xs text-slate-300">
            <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800 font-sans">
                <tr>
                    <th class="px-4 py-3">Campaign</th>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Match / Max Bonus</th>
                    <th class="px-4 py-3">Rollover Req</th>
                    <th class="px-4 py-3">Claims Count</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 font-mono">
                @forelse($promotions as $promo)
                    <tr>
                        <td class="px-4 py-3 font-sans">
                            <p class="font-bold text-white">{{ $promo->title_en }}</p>
                            <span class="text-[10px] text-slate-400">{{ $promo->title_id }}</span>
                        </td>
                        <td class="px-4 py-3 text-amber-400 font-bold">{{ $promo->code }}</td>
                        <td class="px-4 py-3 font-sans capitalize text-slate-300">{{ str_replace('_', ' ', $promo->promo_type) }}</td>
                        <td class="px-4 py-3 font-bold text-white">
                            @if($promo->promo_type === 'deposit_match')
                                {{ $promo->match_percentage }}% up to {{ format_usd($promo->max_bonus_usd) }}
                            @else
                                Fixed {{ format_usd($promo->fixed_bonus_usd) }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-amber-300 font-bold">{{ $promo->wagering_multiplier }}x</td>
                        <td class="px-4 py-3 text-slate-300">{{ $promo->player_bonuses_count }} players</td>
                        <td class="px-4 py-3 font-sans">
                            <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $promo->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-400' }}">
                                {{ $promo->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-sans">
                            <form method="POST" action="{{ route('admin.promotions.toggle', $promo->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded-lg {{ $promo->status === 'active' ? 'bg-amber-500/10 text-amber-300' : 'bg-emerald-500/10 text-emerald-400' }} text-[11px] font-semibold">
                                    {{ $promo->status === 'active' ? 'Pause' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-6 text-center text-slate-500 font-sans">No promotional campaigns found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Create Promotion Modal -->
    <div x-show="newPromoModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="newPromoModal = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-slate-900 rounded-2xl border border-slate-800 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6 space-y-4">
                <h3 class="text-base font-bold text-white">Create New Promotional Campaign</h3>

                <form method="POST" action="{{ route('admin.promotions.store') }}" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block font-bold text-slate-300">Campaign Code</label>
                            <input type="text" name="code" placeholder="e.g. RELOAD100" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono uppercase">
                        </div>
                        <div class="space-y-1">
                            <label class="block font-bold text-slate-300">Promo Type</label>
                            <select name="promo_type" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white">
                                <option value="deposit_match">Deposit Match %</option>
                                <option value="free_chip">Free Chip Bonus</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block font-bold text-slate-300">English Title</label>
                        <input type="text" name="title_en" placeholder="100% Reload Bonus" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white">
                    </div>

                    <div class="space-y-1">
                        <label class="block font-bold text-slate-300">Indonesian Title</label>
                        <input type="text" name="title_id" placeholder="Bonus Deposit 100%" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white">
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="space-y-1">
                            <label class="block font-bold text-slate-300">Match %</label>
                            <input type="number" step="5" name="match_percentage" value="100" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono">
                        </div>
                        <div class="space-y-1">
                            <label class="block font-bold text-slate-300">Max USD</label>
                            <input type="number" step="10" name="max_bonus_usd" value="200" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono">
                        </div>
                        <div class="space-y-1">
                            <label class="block font-bold text-slate-300">Rollover Multiplier</label>
                            <input type="number" step="1" name="wagering_multiplier" value="25" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                        <button @click="newPromoModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-bold hover:bg-amber-400">Create Campaign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
