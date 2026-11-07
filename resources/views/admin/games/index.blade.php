@extends('layouts.admin')

@section('title', 'Game Catalog & Aggregator Providers — NexusPlay Backoffice')

@section('content')
<div class="space-y-6" x-data="{
    limitModal: false,
    selectedGame: null,
    minBet: 0.10,
    maxBet: 500,
    rtp: 96.5,
    openLimits(game) {
        this.selectedGame = game;
        this.minBet = game.min_bet_usd;
        this.maxBet = game.max_bet_usd;
        this.rtp = game.rtp_percentage;
        this.limitModal = true;
    }
}">

    <!-- Header & Search -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white">Aggregator Games Catalog</h1>
            <p class="text-xs text-slate-400">Total: <strong class="text-white">{{ $games->total() }}</strong> integrated titles across 5 providers</p>
        </div>

        <form method="GET" action="{{ route('admin.games.index') }}" class="flex items-center gap-2 max-w-md w-full">
            <select name="provider" class="bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white">
                <option value="">All Providers</option>
                @foreach($providers as $p)
                    <option value="{{ $p->id }}" {{ $providerFilter == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
            <input type="text" name="q" value="{{ $search }}" placeholder="Search games..." class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500">
            <button type="submit" class="px-3 py-2 rounded-xl bg-slate-800 text-slate-200 text-xs font-semibold shrink-0">Filter</button>
        </form>
    </div>

    <!-- Games Table -->
    <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
        <table class="w-full text-left text-xs text-slate-300">
            <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800 font-sans">
                <tr>
                    <th class="px-4 py-3">Game Title</th>
                    <th class="px-4 py-3">Provider</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">RTP %</th>
                    <th class="px-4 py-3">Min / Max Bet</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 font-mono text-xs">
                @forelse($games as $game)
                    <tr>
                        <td class="px-4 py-3 font-sans">
                            <p class="font-bold text-white">{{ $game->title }}</p>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $game->game_code }}</span>
                        </td>
                        <td class="px-4 py-3 font-sans text-slate-300">{{ $game->provider->name }}</td>
                        <td class="px-4 py-3 font-sans capitalize text-slate-400">{{ $game->category }}</td>
                        <td class="px-4 py-3 font-bold text-emerald-400">{{ $game->rtp_percentage }}%</td>
                        <td class="px-4 py-3 text-slate-300">${{ number_format($game->min_bet_usd, 2) }} / ${{ number_format($game->max_bet_usd, 2) }}</td>
                        <td class="px-4 py-3 font-sans">
                            <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $game->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-300' }}">
                                {{ $game->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-sans">
                            <div class="flex items-center justify-end gap-2">
                                <button @click="openLimits({{ json_encode($game) }})" type="button" class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 hover:text-white text-[11px] font-semibold">
                                    Limits
                                </button>
                                <form method="POST" action="{{ route('admin.games.toggle', $game->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-lg {{ $game->status === 'active' ? 'bg-amber-500/10 text-amber-300 hover:bg-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' }} text-[11px] font-semibold">
                                        {{ $game->status === 'active' ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500 font-sans">No games found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div>
        {{ $games->links() }}
    </div>

    <!-- Edit Limits Modal -->
    <div x-show="limitModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="limitModal = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-slate-900 rounded-2xl border border-slate-800 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6 space-y-4">
                <h3 class="text-base font-bold text-white">Update Betting Limits &amp; RTP</h3>
                <p class="text-xs text-slate-400 font-mono" x-text="selectedGame ? selectedGame.title : ''"></p>

                <form method="POST" :action="'/admin/games/' + (selectedGame ? selectedGame.id : '') + '/limits'" class="space-y-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block font-bold text-slate-300">Min Bet (USD)</label>
                            <input type="number" step="0.10" name="min_bet_usd" x-model="minBet" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono">
                        </div>
                        <div class="space-y-1">
                            <label class="block font-bold text-slate-300">Max Bet (USD)</label>
                            <input type="number" step="5" name="max_bet_usd" x-model="maxBet" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block font-bold text-slate-300">Target RTP (%)</label>
                        <input type="number" step="0.01" min="80" max="99.99" name="rtp_percentage" x-model="rtp" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white font-mono">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                        <button @click="limitModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-bold hover:bg-amber-400">Save Limits</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
