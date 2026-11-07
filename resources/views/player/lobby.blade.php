@extends('layouts.player')

@section('title', 'Game Lobby — NexusPlay')

@section('content')
<div class="space-y-8" x-data="{
    favoriteIds: {{ json_encode($userFavorites) }},
    toggleFav(gameId) {
        fetch('{{ route('lobby.favorite') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ game_id: gameId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.is_favorite) {
                if (!this.favoriteIds.includes(gameId)) this.favoriteIds.push(gameId);
            } else {
                this.favoriteIds = this.favoriteIds.filter(id => id !== gameId);
            }
        });
    }
}">

    <!-- Featured Banner / Promotions Mini-Hero -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-950 via-slate-900 to-indigo-950 border border-slate-800 p-6 sm:p-8">
        <div class="max-w-2xl space-y-3 relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Featured Releases &bull; Seamless USD Ledger Active</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                NexusPlay Casino &amp; Instant Games
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                Provably fair simulations, instant USD wagers, and multi-provider aggregations. Select any title below to test live bet telemetry, round settlement, and atomic rollbacks.
            </p>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="space-y-4">
        <!-- Top Row: Search & Favorites toggle -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <!-- Search Form -->
            <form method="GET" action="{{ route('lobby.index') }}" class="relative flex-1 max-w-md">
                <input type="hidden" name="category" value="{{ $category }}">
                @if($providerSlug)
                    <input type="hidden" name="provider" value="{{ $providerSlug }}">
                @endif
                <input type="text" name="q" value="{{ $search }}" placeholder="Search games or providers..." class="w-full bg-slate-900 border border-slate-800 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </form>

            <!-- Quick Favorites Button -->
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('lobby.index', array_merge(request()->query(), ['favorites' => $onlyFavorites ? 0 : 1])) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border text-xs font-semibold transition {{ $onlyFavorites ? 'bg-rose-500/20 border-rose-500/50 text-rose-300' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white' }}">
                    <svg class="w-4 h-4 {{ $onlyFavorites ? 'fill-rose-400 text-rose-400' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span>My Favorites</span>
                </a>
            </div>
        </div>

        <!-- Category Pills (Overflow-x scrollable without clipping) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
            @foreach($categories as $catKey => $catLabel)
                <a href="{{ route('lobby.index', array_merge(request()->except('category', 'page'), ['category' => $catKey])) }}"
                   class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition {{ $category === $catKey ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800' }}">
                    {{ $catLabel }}
                </a>
            @endforeach
        </div>

        <!-- Provider Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none text-[11px]">
            <span class="text-slate-500 font-medium shrink-0">Providers:</span>
            <a href="{{ route('lobby.index', request()->except('provider', 'page')) }}"
               class="px-2.5 py-1 rounded-lg transition {{ empty($providerSlug) ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:text-white' }}">
                All
            </a>
            @foreach($providers as $prov)
                <a href="{{ route('lobby.index', array_merge(request()->except('provider', 'page'), ['provider' => $prov->code])) }}"
                   class="px-2.5 py-1 rounded-lg transition {{ $providerSlug === $prov->code ? 'bg-slate-800 text-emerald-400 font-bold' : 'text-slate-400 hover:text-white' }}">
                    {{ $prov->name }} ({{ $prov->games_count }})
                </a>
            @endforeach
        </div>
    </div>

    <!-- Games Grid (Responsive & Anti-Overflow) -->
    @if($games->isEmpty())
        <div class="text-center py-16 bg-slate-900/50 rounded-2xl border border-slate-800 p-8 space-y-3">
            <svg class="w-12 h-12 text-slate-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-semibold text-slate-300">No games matched your active filters.</p>
            <a href="{{ route('lobby.index') }}" class="inline-block text-xs text-emerald-400 hover:underline">Reset Filters</a>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($games as $game)
                @php
                    $isFav = in_array($game->id, $userFavorites);
                    $themeGrad = match($game->category) {
                        'slots' => 'from-amber-600 to-rose-700',
                        'crash' => 'from-emerald-600 to-teal-800',
                        'live' => 'from-purple-700 to-indigo-900',
                        'table' => 'from-blue-700 to-cyan-900',
                        default => 'from-slate-700 to-slate-900',
                    };
                @endphp

                <div class="group relative flex flex-col justify-between rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-lg hover:border-slate-700 transition transform hover:-translate-y-1">

                    <!-- Thumbnail Simulation -->
                    <div class="relative h-36 bg-gradient-to-br {{ $themeGrad }} flex flex-col items-center justify-center p-3 text-center">
                        <!-- Provider Tag -->
                        <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-md bg-slate-950/70 backdrop-blur text-[10px] font-bold text-slate-300">
                            {{ $game->provider->name }}
                        </span>

                        <!-- Favorite Heart Button -->
                        <button @click="toggleFav({{ $game->id }})" type="button" class="absolute top-2.5 right-2.5 p-1.5 rounded-full bg-slate-950/70 text-slate-400 hover:text-rose-400 transition" aria-label="Favorite">
                            <svg class="w-4 h-4 transition" :class="favoriteIds.includes({{ $game->id }}) ? 'fill-rose-500 text-rose-500' : 'text-slate-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>

                        <span class="text-3xl mb-1">
                            @if($game->category === 'slots') 🎰
                            @elseif($game->category === 'crash') 🚀
                            @elseif($game->category === 'live') ♠️
                            @else 🎲 @endif
                        </span>
                        <p class="font-extrabold text-white text-xs sm:text-sm drop-shadow line-clamp-1">{{ $game->title }}</p>
                    </div>

                    <!-- Meta Details & Play Button -->
                    <div class="p-3 space-y-2.5">
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span>RTP: <strong class="text-emerald-400 font-mono">{{ $game->rtp_percentage }}%</strong></span>
                            <span>Min: <strong class="text-slate-300 font-mono">${{ number_format($game->min_bet_usd, 2) }}</strong></span>
                        </div>

                        <a href="{{ route('play.launch', $game->game_code) }}" class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-400 text-slate-950 font-bold text-xs hover:from-emerald-400 hover:to-emerald-300 shadow-md shadow-emerald-500/20 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            </svg>
                            <span>{{ __('ui.nav.play') }}</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
