@extends('layouts.public')

@section('title', 'NexusPlay — Turnkey iGaming Platform, Seamless Wallet & Backoffice Demo')

@section('content')
<!-- Hero Section -->
<section class="relative overflow-hidden py-12 md:py-20 lg:py-24 border-b border-slate-800/80 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950">
    <!-- Ambient glow decorations (contained) -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/3 right-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-3xl mx-auto space-y-6">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Ready-to-Deploy White-Label iGaming Solution</span>
            </div>

            <!-- Headline -->
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight">
                @if(app()->getLocale() === 'id')
                    Platform iGaming Turnkey, Dompet USD Atomik & Backoffice
                @else
                    Turnkey iGaming Platform, Atomic USD Wallet & Backoffice
                @endif
            </h1>

            <!-- Subtitle -->
            <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl mx-auto">
                @if(app()->getLocale() === 'id')
                    Solusi komersial terpadu berbasis Laravel 13, database MySQL berpresisi penuh USD ($), integrasi Game Aggregator API simulator, kasir payment gateway bersegel HMAC-SHA256, serta panel backoffice mutakhir.
                @else
                    Commercial-grade architecture powered by Laravel 13, exact-precision USD ($) multi-balance ledger, simulated Game Aggregator API, signed Payment Gateway webhook engine, and data-dense backoffice.
                @endif
            </p>

            <!-- Dual CTA Group (flex-wrap anti-overflow) -->
            <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                <a href="{{ route('demo.login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-400 text-slate-950 font-bold text-sm sm:text-base hover:from-emerald-400 hover:to-emerald-300 shadow-xl shadow-emerald-500/25 transition-all transform hover:-translate-y-0.5">
                    <span>{{ __('ui.nav.explore_demo') }}</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
                <a href="{{ route('public.docs') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-slate-900 text-slate-200 hover:text-white font-semibold text-sm sm:text-base border border-slate-700 hover:border-slate-600 transition">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>{{ __('ui.nav.docs') }}</span>
                </a>
            </div>

            <!-- Live Sample Metrics Bar -->
            <div class="pt-8">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 rounded-2xl bg-slate-900/80 border border-slate-800 backdrop-blur-sm max-w-3xl mx-auto">
                    <div class="text-center p-2">
                        <div class="text-xl sm:text-2xl font-black text-white font-mono">{{ $metrics['total_games'] }}</div>
                        <div class="text-[11px] text-slate-400 font-medium">Catalog Games</div>
                    </div>
                    <div class="text-center p-2 border-l border-slate-800">
                        <div class="text-xl sm:text-2xl font-black text-emerald-400 font-mono">{{ $metrics['total_providers'] }}</div>
                        <div class="text-[11px] text-slate-400 font-medium">Game Providers</div>
                    </div>
                    <div class="text-center p-2 border-t sm:border-t-0 sm:border-l border-slate-800">
                        <div class="text-xl sm:text-2xl font-black text-amber-400 font-mono">100%</div>
                        <div class="text-[11px] text-slate-400 font-medium">USD ($) Precision</div>
                    </div>
                    <div class="text-center p-2 border-t sm:border-t-0 sm:border-l border-slate-800">
                        <div class="text-xl sm:text-2xl font-black text-sky-400 font-mono">{{ $metrics['seeded_personas'] }}</div>
                        <div class="text-[11px] text-slate-400 font-medium">Demo Personas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Live Featured Games Showcase (Database-Backed) -->
<section id="games" class="py-12 md:py-16 bg-slate-950 border-b border-slate-800/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Live Catalog Preview</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mt-1">
                    Featured Games & Providers
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 max-w-xl">
                    Experience seamless wallet betting across real catalog titles with configurable volatility, RTP, and min/max USD stakes.
                </p>
            </div>
            <a href="{{ route('demo.login') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-emerald-400 hover:text-emerald-300 transition">
                <span>View All Games in Lobby</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>

        <!-- 8 Featured Games Grid (Responsive 2 cols on mobile, 4 cols on desktop) -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
            @foreach($featuredGames as $game)
                <div class="group relative rounded-2xl bg-slate-900 border border-slate-800 hover:border-emerald-500/50 transition-all duration-300 overflow-hidden flex flex-col justify-between">
                    <!-- Artwork Header with Gradient -->
                    <div class="aspect-[16/10] w-full bg-gradient-to-br {{ $game->thumbnail_gradient ?? 'from-slate-800 to-indigo-950' }} p-3 flex flex-col justify-between relative overflow-hidden">
                        <div class="flex items-center justify-between gap-1 relative z-10">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-950/80 text-emerald-400 border border-emerald-500/20 backdrop-blur-sm">
                                {{ ucfirst(str_replace('_', ' ', $game->category)) }}
                            </span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono text-amber-300 bg-black/60 backdrop-blur-sm">
                                {{ $game->rtp_percentage }}% RTP
                            </span>
                        </div>
                        <div class="relative z-10">
                            <h3 class="font-bold text-white text-sm sm:text-base leading-tight drop-shadow-md truncate">
                                {{ $game->title }}
                            </h3>
                            <span class="text-[11px] text-slate-300 font-medium">
                                {{ $game->provider->name }}
                            </span>
                        </div>
                        <!-- Decorative sheen -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>
                    </div>

                    <!-- Meta & Action Footer -->
                    <div class="p-3 sm:p-4 space-y-3 bg-slate-900/90 flex-1 flex flex-col justify-between">
                        <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                            <span>Stake:</span>
                            <span class="text-slate-200">{{ format_usd($game->min_bet_usd) }} - {{ format_usd($game->max_bet_usd) }}</span>
                        </div>

                        <a href="{{ route('demo.login') }}" class="w-full text-center py-2 px-3 rounded-xl bg-slate-800 hover:bg-emerald-500 text-slate-200 hover:text-slate-950 font-semibold text-xs transition duration-200 block shadow-sm">
                            {{ __('ui.action.launch_demo') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Core Turnkey Modules (6 Cards) -->
<section id="features" class="py-12 md:py-16 bg-slate-900/60 border-b border-slate-800/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Modular Architecture</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mt-1">
                Engineered for Real-World iGaming Operations
            </h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-2">
                Every subsystem operates with real database transactions, atomic locking, and strict separation of concerns.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            <!-- 1. Seamless Single-Wallet Engine -->
            <div class="p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-white">Seamless Single-Wallet Engine (USD)</h3>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                    Zero-discrepancy double-entry ledger separating Real Cash, Bonus Balance, and Locked Withdrawal funds under atomic row-level locks.
                </p>
            </div>

            <!-- 2. Game API Aggregator Simulator -->
            <div class="p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/>
                    </svg>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-white">Game API Aggregator Simulator</h3>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                    Deterministic round testing (Loss, 2.5x Win, 15x Big Win, Timeout Rollback) signed with HMAC-SHA256 and verified for replay protection.
                </p>
            </div>

            <!-- 3. Payment Gateway Webhook Engine -->
            <div class="p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-400 border border-sky-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-white">Signed Payment Gateway Cashier</h3>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                    Interactive sandbox for Crypto (USDT), Cards, Bank Wire, and E-Wallets. Test paid, failed, expired, and duplicate replay callbacks.
                </p>
            </div>

            <!-- 4. Promotions & VIP Engine -->
            <div class="p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                    </svg>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-white">Bonus Rollover & VIP Tiers</h3>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                    Automated turnover tracker across 4 tiers (Bronze to Platinum). Bets contribute toward turnover and auto-convert unlocked bonus to cash.
                </p>
            </div>

            <!-- 5. Affiliate Portal & NGR Accounting -->
            <div class="p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 border border-rose-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-white">Affiliate NGR & Commission Portal</h3>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                    Referral link tracking, player attribution, and Net Gaming Revenue formula ($NGR = GGR - Bonus Cost - 15\%$) commission payout requests.
                </p>
            </div>

            <!-- 6. Backoffice & Security Audit Trail -->
            <div class="p-5 sm:p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                <div class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-400 border border-teal-500/20 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-base sm:text-lg font-bold text-white">Backoffice & SHA-256 Audit Trail</h3>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                    Executive KPI dashboard, high-value withdrawal dual control ($1,000 threshold), manual adjustments, and chained audit logs.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- 4-Step Architecture Workflow Section -->
<section id="architecture" class="py-12 md:py-16 bg-slate-950 border-b border-slate-800/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">End-to-End Operational Lifecycle</span>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mt-1">
                From Player Deposit to Backoffice Reconciliation
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Step 1 -->
            <div class="p-5 rounded-xl bg-slate-900/60 border border-slate-800 relative space-y-2">
                <span class="text-2xl font-black text-slate-700 font-mono">01</span>
                <h4 class="font-bold text-white text-sm">Persona Authentication</h4>
                <p class="text-xs text-slate-400">1-click persona switch without manual credentials. Real Laravel sessions with role-based policies.</p>
            </div>
            <!-- Step 2 -->
            <div class="p-5 rounded-xl bg-slate-900/60 border border-slate-800 relative space-y-2">
                <span class="text-2xl font-black text-slate-700 font-mono">02</span>
                <h4 class="font-bold text-white text-sm">USD Deposit & Bonus</h4>
                <p class="text-xs text-slate-400">Simulate crypto, card, or wire funding with signed HMAC-SHA256 webhook confirmation and rollover tracking.</p>
            </div>
            <!-- Step 3 -->
            <div class="p-5 rounded-xl bg-slate-900/60 border border-slate-800 relative space-y-2">
                <span class="text-2xl font-black text-slate-700 font-mono">03</span>
                <h4 class="font-bold text-white text-sm">Seamless Game Settlement</h4>
                <p class="text-xs text-slate-400">Atomic real-money-first bet deductions, instant win payouts, and idempotent provider rollback handling.</p>
            </div>
            <!-- Step 4 -->
            <div class="p-5 rounded-xl bg-slate-900/60 border border-slate-800 relative space-y-2">
                <span class="text-2xl font-black text-slate-700 font-mono">04</span>
                <h4 class="font-bold text-white text-sm">Dual-Control Payouts</h4>
                <p class="text-xs text-slate-400">Withdrawals lock in USD. Staff approves standard requests; Super Admin reviews high-value payouts ($1k+).</p>
            </div>
        </div>
    </div>
</section>

<!-- Mandatory Customization Notice Section -->
<section class="py-12 md:py-16 bg-slate-900/80">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950/40 border border-emerald-500/30 shadow-2xl relative overflow-hidden space-y-4">
            <div class="flex items-center gap-2 text-emerald-400 text-xs font-bold uppercase tracking-wider">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>White-Label Customization Notice</span>
            </div>

            <h3 class="text-xl sm:text-2xl font-bold text-white">
                {{ __('ui.customization_notice_title') }}
            </h3>

            <p class="text-sm text-slate-300 leading-relaxed">
                {{ __('ui.customization_notice_body') }}
            </p>

            <div class="pt-2 flex flex-wrap items-center gap-4">
                <a href="mailto:{{ config('demo.contact_email') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-sm shadow-lg shadow-emerald-500/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ config('demo.contact_email') }}</span>
                </a>
                <a href="{{ route('public.docs') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-slate-300 hover:text-white transition">
                    <span>Read Architecture Guide</span>
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
