@extends('layouts.player')

@section('title', 'Play ' . $game->title . ' — NexusPlay')

@section('content')
<div class="space-y-6" x-data="{
    betAmount: {{ number_format($game->min_bet_usd > 1.0 ? $game->min_bet_usd : 5.00, 2, '.', '') }},
    loading: false,
    roundResult: {{ session('round_result') ? json_encode(session('round_result')) : 'null' }},
    lastOutcome: 'idle',
    inspectTab: 'telemetry',
    playRound(outcome) {
        this.loading = true;
        fetch('{{ route('play.round', $game->game_code) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                bet_amount: this.betAmount,
                outcome_mode: outcome
            })
        })
        .then(res => res.json())
        .then(data => {
            this.loading = false;
            if (data.success) {
                this.roundResult = data.data;
                this.lastOutcome = data.data.outcome_type;
                // Live reload balance display if needed
                window.location.reload();
            } else {
                alert(data.error || 'Failed to execute round');
            }
        })
        .catch(err => {
            this.loading = false;
            console.error(err);
        });
    },
    rollbackRound() {
        this.loading = true;
        fetch('{{ route('play.rollback', $game->game_code) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                bet_amount: this.betAmount
            })
        })
        .then(res => res.json())
        .then(data => {
            this.loading = false;
            if (data.success) {
                this.roundResult = data.data;
                window.location.reload();
            } else {
                alert(data.error || 'Rollback failed');
            }
        });
    }
}">

    <!-- Breadcrumb & Game Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2 border-b border-slate-800">
        <div class="flex items-center gap-3">
            <a href="{{ route('lobby.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2">
                    <span>{{ $game->title }}</span>
                    <span class="text-xs px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-mono">
                        RTP {{ $game->rtp_percentage }}%
                    </span>
                </h1>
                <p class="text-xs text-slate-400">
                    Aggregator: <strong class="text-slate-200">{{ $game->provider->name }}</strong> &bull; Token: <span class="font-mono text-slate-500">{{ substr($session->session_token, 0, 16) }}...</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-xs text-slate-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Active Session</span>
            </span>
        </div>
    </div>

    <!-- Main Game Arena & Side Inspector Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: Interactive Game Stage (8 cols) -->
        <div class="lg:col-span-8 space-y-4">
            <!-- Stage Viewport -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-b from-slate-900 via-slate-950 to-slate-950 border border-slate-800 p-6 sm:p-10 flex flex-col items-center justify-center min-h-[360px] text-center shadow-2xl">

                <!-- Ambient glow -->
                <div class="absolute inset-0 bg-radial from-emerald-500/10 via-transparent to-transparent pointer-events-none"></div>

                <!-- Simulation Graphic -->
                <div class="relative z-10 space-y-4 max-w-md w-full">
                    @if(session('round_result'))
                        @php $res = session('round_result'); @endphp
                        <div class="p-4 rounded-2xl border text-center space-y-2 {{ $res['outcome_type'] === 'big_win' ? 'bg-amber-500/20 border-amber-500/50' : ($res['payout_amount_usd'] > 0 ? 'bg-emerald-500/20 border-emerald-500/50' : ($res['outcome_type'] === 'rolled_back' ? 'bg-indigo-500/20 border-indigo-500/50' : 'bg-slate-900 border-slate-800')) }}">
                            <p class="text-xs uppercase font-bold tracking-wider {{ $res['outcome_type'] === 'big_win' ? 'text-amber-400' : ($res['payout_amount_usd'] > 0 ? 'text-emerald-400' : 'text-slate-400') }}">
                                {{ str_replace('_', ' ', strtoupper($res['outcome_type'])) }}
                            </p>
                            <div class="text-3xl sm:text-4xl font-black text-white">
                                @if($res['payout_amount_usd'] > 0)
                                    +{{ format_usd($res['payout_amount_usd']) }}
                                    <span class="text-xs text-emerald-400 font-mono block">({{ $res['multiplier'] }}x Multiplier)</span>
                                @elseif($res['outcome_type'] === 'rolled_back')
                                    <span class="text-indigo-300">Wager Restored</span>
                                    <span class="text-xs text-indigo-400 font-mono block">(Atomic Rollback)</span>
                                @else
                                    <span class="text-slate-400">-{{ format_usd($res['bet_amount_usd']) }}</span>
                                @endif
                            </div>
                            <p class="text-[11px] font-mono text-slate-400">Round: {{ $res['round_code'] }}</p>
                        </div>
                    @else
                        <div class="space-y-2">
                            <span class="text-6xl animate-bounce inline-block">
                                @if($game->category === 'slots') 🎰
                                @elseif($game->category === 'crash') 🚀
                                @elseif($game->category === 'live') ♠️
                                @else 🎲 @endif
                            </span>
                            <h3 class="text-xl font-extrabold text-white">Ready for Wager</h3>
                            <p class="text-xs text-slate-400">Choose a bet amount and trigger a test outcome below.</p>
                        </div>
                    @endif
                </div>

                <!-- Loading Overlay -->
                <div x-show="loading" x-cloak class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm flex flex-col items-center justify-center z-30">
                    <div class="w-10 h-10 border-4 border-emerald-500 border-t-transparent rounded-full animate-spin mb-3"></div>
                    <span class="text-xs font-bold text-white uppercase tracking-wider">Signing Aggregator Round...</span>
                </div>
            </div>

            <!-- Controls Panel -->
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Select Bet Amount (USD)</span>
                    <!-- Quick Bet Chips -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        @foreach([0.50, 1.00, 5.00, 20.00, 50.00, 100.00] as $preset)
                            <button @click="betAmount = {{ number_format($preset, 2, '.', '') }}" type="button"
                                    :class="betAmount == {{ $preset }} ? 'bg-emerald-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-300 hover:text-white'"
                                    class="px-2.5 py-1 rounded-lg text-xs transition">
                                ${{ number_format($preset, 2) }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Bet Input & Outcome Triggers -->
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative w-full sm:w-44">
                        <span class="absolute left-3 top-2.5 text-xs text-slate-400 font-bold">$</span>
                        <input type="number" step="0.50" min="{{ $game->min_bet_usd }}" max="{{ $game->max_bet_usd }}" x-model="betAmount" class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-7 pr-3 py-2 text-xs font-mono font-bold text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- 5 Trigger Buttons -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 w-full">
                        <button @click="playRound('random')" type="button" class="py-2.5 px-3 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-400 text-slate-950 font-bold text-xs hover:from-emerald-400 hover:to-emerald-300 transition shadow-md shadow-emerald-500/20">
                            Spin (RTP)
                        </button>
                        <button @click="playRound('win_small')" type="button" class="py-2.5 px-3 rounded-xl bg-blue-600/30 border border-blue-500/40 text-blue-300 font-bold text-xs hover:bg-blue-600/40 transition">
                            Win (2.5x)
                        </button>
                        <button @click="playRound('win_big')" type="button" class="py-2.5 px-3 rounded-xl bg-amber-500/30 border border-amber-500/40 text-amber-300 font-bold text-xs hover:bg-amber-500/40 transition">
                            Big Win (15x)
                        </button>
                        <button @click="playRound('loss')" type="button" class="py-2.5 px-3 rounded-xl bg-rose-600/30 border border-rose-500/40 text-rose-300 font-bold text-xs hover:bg-rose-600/40 transition">
                            Force Loss
                        </button>
                        <button @click="rollbackRound()" type="button" class="py-2.5 px-3 rounded-xl bg-indigo-600/30 border border-indigo-500/40 text-indigo-300 font-bold text-xs hover:bg-indigo-600/40 transition col-span-2 sm:col-span-1">
                            Rollback
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Real-time Telemetry & Signed Webhook Inspector (4 cols) -->
        <div class="lg:col-span-4 space-y-4">
            <div class="rounded-2xl bg-slate-900 border border-slate-800 p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <span class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        Aggregator Telemetry
                    </span>
                    <div class="flex items-center gap-1 text-[10px]">
                        <button @click="inspectTab = 'telemetry'" :class="inspectTab === 'telemetry' ? 'bg-slate-800 text-emerald-400 font-bold' : 'text-slate-400'" class="px-2 py-0.5 rounded">
                            Payload
                        </button>
                        <button @click="inspectTab = 'rounds'" :class="inspectTab === 'rounds' ? 'bg-slate-800 text-emerald-400 font-bold' : 'text-slate-400'" class="px-2 py-0.5 rounded">
                            History
                        </button>
                    </div>
                </div>

                <!-- Inspector View -->
                <div x-show="inspectTab === 'telemetry'" class="space-y-3 text-xs">
                    @if(session('round_result'))
                        @php $r = session('round_result'); @endphp
                        <div>
                            <p class="text-[10px] text-slate-400 uppercase font-semibold">HMAC-SHA256 Signature</p>
                            <div class="p-2 rounded-lg bg-slate-950 font-mono text-[10px] text-emerald-400 break-all select-all">
                                {{ $r['webhook_signature'] }}
                            </div>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-400 uppercase font-semibold mb-1">Raw Signed JSON</p>
                            <pre class="p-2.5 rounded-lg bg-slate-950 font-mono text-[10px] text-slate-300 overflow-x-auto max-h-56 scrollbar-none">{{ json_encode($r['webhook_payload'], JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @else
                        <div class="py-8 text-center text-slate-500 space-y-1">
                            <svg class="w-8 h-8 mx-auto text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                            <p class="text-xs">Place a bet to inspect the signed aggregator payload.</p>
                        </div>
                    @endif
                </div>

                <!-- History View -->
                <div x-show="inspectTab === 'rounds'" x-cloak class="space-y-2 text-xs">
                    @forelse($recentRounds as $round)
                        <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                            <div>
                                <p class="font-mono text-[11px] text-slate-300">{{ $round->round_code }}</p>
                                <span class="text-[10px] text-slate-500">{{ $round->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-mono font-bold text-xs {{ $round->payout_amount_usd > 0 ? 'text-emerald-400' : 'text-slate-400' }}">
                                    {{ $round->payout_amount_usd > 0 ? '+' . format_usd($round->payout_amount_usd) : '-' . format_usd($round->bet_amount_usd) }}
                                </span>
                                <span class="block text-[10px] uppercase font-bold text-slate-500">{{ $round->outcome_type }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-center py-6 text-slate-500 text-xs">No rounds recorded yet in this session.</p>
                    @endforelse
                </div>
            </div>

            <!-- Active Rollover Widget if user has active bonus -->
            @if($activeBonus)
                <div class="rounded-2xl bg-amber-500/10 border border-amber-500/30 p-4 space-y-2 text-xs">
                    <div class="flex items-center justify-between text-amber-300 font-bold">
                        <span>Active Bonus Rollover</span>
                        <span>{{ number_format($activeBonus->progressPercentage(), 1) }}%</span>
                    </div>
                    <div class="w-full bg-slate-950 rounded-full h-2 overflow-hidden border border-amber-500/20">
                        <div class="bg-amber-400 h-2 rounded-full" style="width: {{ $activeBonus->progressPercentage() }}%"></div>
                    </div>
                    <div class="flex justify-between text-[10px] text-slate-400 font-mono">
                        <span>Wagered: {{ format_usd($activeBonus->wagering_completed_usd) }}</span>
                        <span>Req: {{ format_usd($activeBonus->wagering_required_usd) }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
