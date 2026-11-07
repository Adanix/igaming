@extends('layouts.public')

@section('title', 'Platform Documentation — NexusPlay iGaming Architecture & API Spec')

@section('content')
<div class="bg-slate-950 text-slate-100 min-h-screen" x-data="{
    activeSection: 'sec-1',
    mobileTocOpen: false,
    scrollTo(id) {
        this.activeSection = id;
        this.mobileTocOpen = false;
        const el = document.getElementById(id);
        if (el) {
            const offset = 80;
            const bodyRect = document.body.getBoundingClientRect().top;
            const elementRect = el.getBoundingClientRect().top;
            const elementPosition = elementRect - bodyRect;
            const offsetPosition = elementPosition - offset;
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }
    }
}">

    <!-- Header Banner -->
    <div class="border-b border-slate-800 bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-emerald-400 uppercase tracking-wider mb-2">
                        <span>NexusPlay Engineering Docs</span>
                        <span>&bull;</span>
                        <span>API &amp; Architecture Reference</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight">
                        Platform Documentation &amp; Specifications
                    </h1>
                    <p class="text-sm sm:text-base text-slate-400 mt-1 max-w-2xl">
                        Comprehensive implementation guide for the Seamless Wallet protocol, simulated math engines, HMAC webhook security, and multi-tier revenue share.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('demo.login') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-400 text-slate-950 font-bold text-xs sm:text-sm hover:from-emerald-400 hover:to-emerald-300 shadow-md shadow-emerald-500/20 transition">
                        <span>Launch Interactive Demo</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile TOC Accordion Button (visible < 1024px) -->
    <div class="lg:hidden sticky top-16 z-30 bg-slate-900/95 backdrop-blur border-b border-slate-800 px-4 py-2.5">
        <button @click="mobileTocOpen = !mobileTocOpen" class="w-full flex items-center justify-between text-xs font-semibold text-slate-200 py-1.5 px-3 rounded-lg bg-slate-800 border border-slate-700">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/>
                </svg>
                <span>Jump to Documentation Section</span>
            </span>
            <svg class="w-4 h-4 transition-transform duration-200" :class="mobileTocOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="mobileTocOpen" x-cloak class="mt-2 py-2 px-1 bg-slate-900 rounded-lg border border-slate-800 space-y-1">
            <button @click="scrollTo('sec-1')" class="w-full text-left px-3 py-1.5 text-xs rounded text-slate-300 hover:text-white hover:bg-slate-800">1. Architecture &amp; Seamless Wallet</button>
            <button @click="scrollTo('sec-2')" class="w-full text-left px-3 py-1.5 text-xs rounded text-slate-300 hover:text-white hover:bg-slate-800">2. Game Aggregator &amp; Telemetry</button>
            <button @click="scrollTo('sec-3')" class="w-full text-left px-3 py-1.5 text-xs rounded text-slate-300 hover:text-white hover:bg-slate-800">3. Payment Gateway &amp; Webhooks</button>
            <button @click="scrollTo('sec-4')" class="w-full text-left px-3 py-1.5 text-xs rounded text-slate-300 hover:text-white hover:bg-slate-800">4. RBAC &amp; Admin MFA</button>
            <button @click="scrollTo('sec-5')" class="w-full text-left px-3 py-1.5 text-xs rounded text-slate-300 hover:text-white hover:bg-slate-800">5. Affiliate Revenue Share (NGR)</button>
            <button @click="scrollTo('sec-6')" class="w-full text-left px-3 py-1.5 text-xs rounded text-slate-300 hover:text-white hover:bg-slate-800">6. Demo Personas &amp; Testing Guide</button>
        </div>
    </div>

    <!-- Main Content Container with Sticky TOC -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Sticky Sidebar TOC (Desktop >= 1024px) -->
            <aside class="hidden lg:block lg:col-span-3 sticky top-24 self-start">
                <nav class="space-y-1 bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 px-2">Table of Contents</p>
                    <a @click.prevent="scrollTo('sec-1')" href="#sec-1"
                       :class="activeSection === 'sec-1' ? 'bg-emerald-500/10 text-emerald-400 border-l-2 border-emerald-400 font-semibold' : 'text-slate-400 hover:text-slate-200'"
                       class="block px-3 py-2 text-xs rounded transition">
                        1. Architecture &amp; Seamless Wallet
                    </a>
                    <a @click.prevent="scrollTo('sec-2')" href="#sec-2"
                       :class="activeSection === 'sec-2' ? 'bg-emerald-500/10 text-emerald-400 border-l-2 border-emerald-400 font-semibold' : 'text-slate-400 hover:text-slate-200'"
                       class="block px-3 py-2 text-xs rounded transition">
                        2. Game Aggregator &amp; Telemetry
                    </a>
                    <a @click.prevent="scrollTo('sec-3')" href="#sec-3"
                       :class="activeSection === 'sec-3' ? 'bg-emerald-500/10 text-emerald-400 border-l-2 border-emerald-400 font-semibold' : 'text-slate-400 hover:text-slate-200'"
                       class="block px-3 py-2 text-xs rounded transition">
                        3. Payment Gateway &amp; Webhooks
                    </a>
                    <a @click.prevent="scrollTo('sec-4')" href="#sec-4"
                       :class="activeSection === 'sec-4' ? 'bg-emerald-500/10 text-emerald-400 border-l-2 border-emerald-400 font-semibold' : 'text-slate-400 hover:text-slate-200'"
                       class="block px-3 py-2 text-xs rounded transition">
                        4. RBAC &amp; Admin MFA
                    </a>
                    <a @click.prevent="scrollTo('sec-5')" href="#sec-5"
                       :class="activeSection === 'sec-5' ? 'bg-emerald-500/10 text-emerald-400 border-l-2 border-emerald-400 font-semibold' : 'text-slate-400 hover:text-slate-200'"
                       class="block px-3 py-2 text-xs rounded transition">
                        5. Affiliate Revenue Share
                    </a>
                    <a @click.prevent="scrollTo('sec-6')" href="#sec-6"
                       :class="activeSection === 'sec-6' ? 'bg-emerald-500/10 text-emerald-400 border-l-2 border-emerald-400 font-semibold' : 'text-slate-400 hover:text-slate-200'"
                       class="block px-3 py-2 text-xs rounded transition">
                        6. Demo Personas &amp; Testing
                    </a>
                </nav>

                <!-- Quick Customization Widget -->
                <div class="mt-6 p-4 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-950 border border-emerald-500/30 text-xs space-y-3">
                    <div class="flex items-center gap-2 text-emerald-400 font-bold">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Need Custom Engineering?</span>
                    </div>
                    <p class="text-slate-400 leading-relaxed text-[11px]">
                        We build bespoke aggregator connectors, live crypto payment engines, and turnkey iGaming infrastructure.
                    </p>
                    <a href="mailto:nixresponding@gmail.com?subject=NexusPlay%20Custom%20Deployment%20Inquiry" class="block w-full text-center py-2 px-3 rounded-lg bg-emerald-500 text-slate-950 font-bold hover:bg-emerald-400 transition text-xs">
                        Contact Engineers
                    </a>
                </div>
            </aside>

            <!-- Documentation Content Area -->
            <main class="lg:col-span-9 space-y-12">

                <!-- Prominent Customization Notice Callout (PRD Section 21.4) -->
                <div class="relative overflow-hidden rounded-2xl border-2 border-emerald-500/40 bg-gradient-to-br from-slate-900 via-emerald-950/20 to-slate-900 p-6 sm:p-8 shadow-2xl">
                    <div class="absolute -right-8 -top-8 w-48 h-48 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 relative z-10">
                        <div class="space-y-2 max-w-2xl">
                            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-semibold">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span>Looking for Custom Production Integrations?</span>
                            </div>
                            <h3 class="text-lg sm:text-xl font-bold text-white">
                                Bespoke Turnkey Deployments &amp; Live Aggregator Connectors
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                                This platform is a comprehensive turnkey demonstration built for iGaming operators, aggregators, and fintech partners. Looking for bespoke casino aggregator integrations (Pragmatic Play, Evolution Gaming, Hacksaw, Spribe), custom sportsbooks, real crypto/fiat payment rails, or proprietary backoffice modules? Contact our engineering team for private bespoke deployments:
                                <strong class="text-emerald-400 underline ml-1">nixresponding@gmail.com</strong>
                            </p>
                        </div>
                        <a href="mailto:nixresponding@gmail.com?subject=NexusPlay%20Custom%20Deployment%20Inquiry" class="shrink-0 inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-amber-500 text-slate-950 font-bold text-xs sm:text-sm shadow-lg shadow-emerald-500/20 hover:from-emerald-400 hover:to-amber-400 transition transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span>Inquire About Custom Engineering</span>
                        </a>
                    </div>
                </div>

                <!-- SECTION 1: Architecture & Seamless Wallet Protocol -->
                <section id="sec-1" class="scroll-mt-24 space-y-6 pt-4 border-t border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-sm">1</span>
                        <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Architecture &amp; Seamless Wallet Protocol</h2>
                    </div>

                    <p class="text-sm text-slate-300 leading-relaxed">
                        In modern high-concurrency iGaming architectures, the <strong>Seamless Wallet</strong> protocol eliminates transfer steps between platform and game providers. The operator wallet remains the single authoritative source of truth for USD balances.
                    </p>

                    <!-- Round Flow Diagram -->
                    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Debit &rarr; Game Engine &rarr; Credit Execution Flow</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-center text-xs">
                            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                                <div class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 mx-auto flex items-center justify-center font-bold">1</div>
                                <p class="font-bold text-white">Debit (Bet Placement)</p>
                                <p class="text-slate-400 text-[11px]">Aggregator sends signed debit request with idempotent <code class="text-emerald-400">round_id</code>. Ledger deducts cash/bonus bucket with row-level lock.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                                <div class="w-6 h-6 rounded-full bg-amber-500/20 text-amber-400 mx-auto flex items-center justify-center font-bold">2</div>
                                <p class="font-bold text-white">Engine Evaluation (RNG)</p>
                                <p class="text-slate-400 text-[11px]">Provably fair / deterministic math engine resolves reel stops, crash multiplier, or roulette pocket. Telemetry payload generated.</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                                <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 mx-auto flex items-center justify-center font-bold">3</div>
                                <p class="font-bold text-white">Credit (Payout Settlement)</p>
                                <p class="text-slate-400 text-[11px]">Winning payout credited instantly to player wallet. Rollover wagering accumulated if active bonus is attached.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Ledger Invariants & Double Entry -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-2">
                            <h4 class="font-bold text-emerald-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Strict Ledger Invariant Guarantee
                            </h4>
                            <p class="text-slate-300 leading-relaxed text-[11px]">
                                No balance row in <code class="text-slate-200">wallets</code> is ever modified without an atomic corresponding row inserted into <code class="text-slate-200">wallet_ledger_entries</code>.
                                Balance reconciles with 100% mathematical precision:
                                <br><span class="text-emerald-400 font-mono">wallets.cash_balance_usd == SUM(ledger WHERE bucket = 'cash')</span>
                            </p>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-900/60 border border-slate-800 space-y-2">
                            <h4 class="font-bold text-amber-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                Atomic Rollback Capability
                            </h4>
                            <p class="text-slate-300 leading-relaxed text-[11px]">
                                In case of provider timeouts, network disconnection, or voided rounds, the platform supports atomic rollback. The debited wager is immediately restored to the player's wallet with type <code class="text-amber-300">rollback</code>, preventing fund loss.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- SECTION 2: Game Aggregator & Telemetry -->
                <section id="sec-2" class="scroll-mt-24 space-y-6 pt-8 border-t border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-sm">2</span>
                        <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Simulated Game Aggregator &amp; Telemetry</h2>
                    </div>

                    <p class="text-sm text-slate-300 leading-relaxed">
                        NexusPlay features an in-memory &amp; database simulator replicating enterprise game aggregators like SoftSwiss, Slotegrator, or EveryMatrix. All requests are cryptographically signed with HMAC-SHA256 headers.
                    </p>

                    <!-- Math Engines Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-800 bg-slate-900">
                        <table class="w-full text-left text-xs text-slate-300">
                            <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                                <tr>
                                    <th class="px-4 py-3">Category</th>
                                    <th class="px-4 py-3">Simulation Model</th>
                                    <th class="px-4 py-3">RTP Target</th>
                                    <th class="px-4 py-3">Telemetry Attributes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-white">Video Slots</td>
                                    <td class="px-4 py-3 text-slate-400">5x3 Grid, 20 paylines, scatter bonus feature trigger</td>
                                    <td class="px-4 py-3 text-emerald-400 font-mono">96.50%</td>
                                    <td class="px-4 py-3 font-mono text-[11px]">reels, paylines, free_spins</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-white">Crash Games</td>
                                    <td class="px-4 py-3 text-slate-400">Exponential multiplier growth curve (1.00x &ndash; 100.00x)</td>
                                    <td class="px-4 py-3 text-emerald-400 font-mono">97.00%</td>
                                    <td class="px-4 py-3 font-mono text-[11px]">multiplier, cashout_at, crash_point</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-white">European Roulette</td>
                                    <td class="px-4 py-3 text-slate-400">Single zero (0-36) wheels, inside/outside bets</td>
                                    <td class="px-4 py-3 text-emerald-400 font-mono">97.30%</td>
                                    <td class="px-4 py-3 font-mono text-[11px]">pocket, color, parity, payout_multiplier</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-white">Live Dealer Blackjack</td>
                                    <td class="px-4 py-3 text-slate-400">8-deck shoe, dealer stands on soft 17, 3:2 payout</td>
                                    <td class="px-4 py-3 text-emerald-400 font-mono">99.28%</td>
                                    <td class="px-4 py-3 font-mono text-[11px]">player_hand, dealer_upcard, outcome</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Telemetry Payload Sample -->
                    <div class="rounded-xl border border-slate-800 bg-slate-950 p-4 space-y-2">
                        <div class="flex items-center justify-between text-xs text-slate-400">
                            <span class="font-mono text-emerald-400">Simulated Telemetry Inspector: Sample Round Payload</span>
                            <span class="text-[10px] bg-slate-800 px-2 py-0.5 rounded text-slate-300">application/json</span>
                        </div>
                        <pre class="text-[11px] font-mono text-slate-300 overflow-x-auto p-3 rounded-lg bg-slate-900 border border-slate-800">
{
  "round_id": "RND-984210384729",
  "provider_code": "pragmatic",
  "game_code": "gates-of-olympus",
  "action": "settle",
  "bet_amount_usd": 5.00,
  "win_amount_usd": 25.00,
  "net_result_usd": 20.00,
  "engine_telemetry": {
    "reels": [["ZEUS", "CROWN", "GEM"], ["RING", "ZEUS", "GOBLET"], ["CROWN", "CROWN", "ZEUS"]],
    "multiplier": 5.0,
    "feature_triggered": false
  },
  "timestamp": 1794042988,
  "signature": "hmac_sha256_b3f9c8d1045e7..."
}</pre>
                    </div>
                </section>

                <!-- SECTION 3: Payment Gateway & Webhooks -->
                <section id="sec-3" class="scroll-mt-24 space-y-6 pt-8 border-t border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-sm">3</span>
                        <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Payment Gateway &amp; Webhook Simulator</h2>
                    </div>

                    <p class="text-sm text-slate-300 leading-relaxed">
                        The cashier simulates fiat &amp; crypto payment rails (Stripe Card, USDT TRC20, Bitcoin, Bank Transfer). It features a real interactive payment sandbox where operators can trigger callbacks and test security defenses.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <!-- Deposit Flow & Security -->
                        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
                            <h4 class="font-bold text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                Deposit Webhook Verification
                            </h4>
                            <p class="text-slate-400 text-[11px] leading-relaxed">
                                External callbacks verify an HMAC-SHA256 signature in the <code class="text-emerald-400">X-Signature</code> header.
                                Any signature mismatch immediately returns <code class="text-rose-400">401 Unauthorized</code> without executing wallet crediting.
                            </p>
                            <div class="p-2.5 rounded-lg bg-slate-950 font-mono text-[10px] text-slate-300">
                                hash_hmac('sha256', $rawBody, $webhookSecret)
                            </div>
                        </div>

                        <!-- Anti-Replay & Idempotency -->
                        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
                            <h4 class="font-bold text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                Anti-Replay &amp; Idempotency Protection
                            </h4>
                            <p class="text-slate-400 text-[11px] leading-relaxed">
                                Every callback checks the transaction's current status and idempotency key. If an invoice has already been marked <code class="text-emerald-400">paid</code>, subsequent replay requests are blocked with <code class="text-amber-400">409 Conflict</code>.
                            </p>
                            <div class="p-2.5 rounded-lg bg-slate-950 font-mono text-[10px] text-slate-300">
                                if ($tx->isCompleted()) return response(409, 'Replay Blocked');
                            </div>
                        </div>
                    </div>

                    <!-- Withdrawal Approval Workflow -->
                    <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3 text-xs">
                        <h4 class="font-bold text-white">Withdrawal Dual-Stage Pipeline</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                                <p class="font-semibold text-emerald-400">&lt; $1,000.00 Standard Withdrawal</p>
                                <p class="text-slate-400 text-[11px] mt-1">Funds locked in <code class="text-slate-300">locked_withdrawal</code> bucket immediately. Standard automated processing or fast-track ops review.</p>
                            </div>
                            <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                                <p class="font-semibold text-rose-400">&ge; $1,000.00 High-Value Threshold</p>
                                <p class="text-slate-400 text-[11px] mt-1">Triggers high-priority backoffice review flag. Requires Ops Staff / Super Admin explicit approval with MFA audit logging before release.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 4: RBAC & Admin MFA -->
                <section id="sec-4" class="scroll-mt-24 space-y-6 pt-8 border-t border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-sm">4</span>
                        <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Role-Based Access Control &amp; Admin MFA</h2>
                    </div>

                    <p class="text-sm text-slate-300 leading-relaxed">
                        NexusPlay enforces role-based security across 4 distinct user tiers, complemented by session-bound Multi-Factor Authentication (MFA) on sensitive backoffice routes.
                    </p>

                    <!-- RBAC Matrix Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-800 bg-slate-900">
                        <table class="w-full text-left text-xs text-slate-300">
                            <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                                <tr>
                                    <th class="px-4 py-3">Role</th>
                                    <th class="px-4 py-3">Landing Interface</th>
                                    <th class="px-4 py-3">Lobby &amp; Play</th>
                                    <th class="px-4 py-3">Cashier</th>
                                    <th class="px-4 py-3">Affiliate CRM</th>
                                    <th class="px-4 py-3">Backoffice &amp; Audit</th>
                                    <th class="px-4 py-3">MFA Protected</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-blue-400">Player (Standard / VIP)</td>
                                    <td class="px-4 py-3 font-mono text-[11px]">/lobby</td>
                                    <td class="px-4 py-3 text-emerald-400">&check; Full</td>
                                    <td class="px-4 py-3 text-emerald-400">&check; Full</td>
                                    <td class="px-4 py-3 text-slate-600">&times;</td>
                                    <td class="px-4 py-3 text-slate-600">&times;</td>
                                    <td class="px-4 py-3 text-slate-500">Optional</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-emerald-400">Affiliate Partner</td>
                                    <td class="px-4 py-3 font-mono text-[11px]">/affiliate</td>
                                    <td class="px-4 py-3 text-slate-600">&times;</td>
                                    <td class="px-4 py-3 text-slate-400">Commission Payout</td>
                                    <td class="px-4 py-3 text-emerald-400">&check; Full</td>
                                    <td class="px-4 py-3 text-slate-600">&times;</td>
                                    <td class="px-4 py-3 text-slate-500">Optional</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-amber-400">Ops Finance Staff</td>
                                    <td class="px-4 py-3 font-mono text-[11px]">/admin</td>
                                    <td class="px-4 py-3 text-slate-600">&times;</td>
                                    <td class="px-4 py-3 text-amber-400">Approvals</td>
                                    <td class="px-4 py-3 text-slate-400">Read-Only</td>
                                    <td class="px-4 py-3 text-emerald-400">&check; Full</td>
                                    <td class="px-4 py-3 text-rose-400 font-bold">&check; Enforced (123456)</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3 font-semibold text-rose-400">Super Admin</td>
                                    <td class="px-4 py-3 font-mono text-[11px]">/admin</td>
                                    <td class="px-4 py-3 text-slate-400">Preview</td>
                                    <td class="px-4 py-3 text-emerald-400">&check; Full Override</td>
                                    <td class="px-4 py-3 text-emerald-400">&check; Full</td>
                                    <td class="px-4 py-3 text-emerald-400">&check; Full + Settings</td>
                                    <td class="px-4 py-3 text-rose-400 font-bold">&check; Enforced (123456)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- SECTION 5: Affiliate Multi-Tier Revenue Share -->
                <section id="sec-5" class="scroll-mt-24 space-y-6 pt-8 border-t border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-sm">5</span>
                        <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Affiliate Multi-Tier Revenue Share (NGR Engine)</h2>
                    </div>

                    <p class="text-sm text-slate-300 leading-relaxed">
                        The affiliate commission engine calculates Net Gaming Revenue (NGR) generated by players registered with an affiliate's referral code.
                    </p>

                    <!-- Formula Callout -->
                    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Authoritative NGR Formula</h4>
                        <div class="p-3 rounded-xl bg-slate-950 font-mono text-xs sm:text-sm text-emerald-300 border border-emerald-500/20">
                            NGR = (Total GGR &minus; Player Bonuses Claimed) &times; (1 &minus; Platform Admin Fee 15%)
                        </div>
                        <p class="text-slate-400 text-xs">
                            Where <span class="text-white font-semibold">GGR = Total Wagers &minus; Total Payouts</span>. If GGR is negative (players won more than they wagered), NGR is bounded to $0.00 for the period (no negative carryover applied by default).
                        </p>
                    </div>

                    <!-- Tier Rates -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center text-xs">
                        <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800">
                            <p class="text-slate-400 text-[10px] uppercase font-bold">Tier 1 (&lt; $5,000 NGR)</p>
                            <p class="text-xl font-black text-white mt-1">25%</p>
                            <p class="text-slate-500 text-[10px] mt-0.5">RevShare</p>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800">
                            <p class="text-slate-400 text-[10px] uppercase font-bold">Tier 2 ($5k - $15k NGR)</p>
                            <p class="text-xl font-black text-blue-400 mt-1">30%</p>
                            <p class="text-slate-500 text-[10px] mt-0.5">RevShare</p>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800">
                            <p class="text-slate-400 text-[10px] uppercase font-bold">Tier 3 ($15k - $50k NGR)</p>
                            <p class="text-xl font-black text-amber-400 mt-1">35%</p>
                            <p class="text-slate-500 text-[10px] mt-0.5">RevShare</p>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800">
                            <p class="text-slate-400 text-[10px] uppercase font-bold">Tier 4 (&gt; $50,000 NGR)</p>
                            <p class="text-xl font-black text-emerald-400 mt-1">40%</p>
                            <p class="text-slate-500 text-[10px] mt-0.5">RevShare</p>
                        </div>
                    </div>
                </section>

                <!-- SECTION 6: Demo Personas & Testing Guide -->
                <section id="sec-6" class="scroll-mt-24 space-y-6 pt-8 border-t border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold text-sm">6</span>
                        <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Demo Personas &amp; Testing Guide</h2>
                    </div>

                    <p class="text-sm text-slate-300 leading-relaxed">
                        To evaluate the platform without entering credentials or registering dummy email addresses, select one of the 5 canonical personas on <a href="{{ route('demo.login') }}" class="text-emerald-400 hover:underline font-semibold">/demo/login</a>.
                    </p>

                    <!-- Personas Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-800 bg-slate-900">
                        <table class="w-full text-left text-xs text-slate-300">
                            <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                                <tr>
                                    <th class="px-4 py-3">Persona</th>
                                    <th class="px-4 py-3">Role &amp; VIP</th>
                                    <th class="px-4 py-3">Cash USD</th>
                                    <th class="px-4 py-3">Bonus USD</th>
                                    <th class="px-4 py-3">Locked USD</th>
                                    <th class="px-4 py-3">Recommended Test Scenario</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-white">Alex Chen</p>
                                        <p class="text-[10px] text-slate-400">alex.chen@nexusplay.demo</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-300 text-[10px] font-semibold">Player</span>
                                        <span class="ml-1 text-[10px] text-amber-400">Bronze</span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-emerald-400">$250.00</td>
                                    <td class="px-4 py-3 font-mono text-amber-300">$50.00</td>
                                    <td class="px-4 py-3 font-mono text-slate-400">$50.00</td>
                                    <td class="px-4 py-3 text-slate-300">Play Gates of Olympus, trigger bonus rollover increments, test standard withdrawal.</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-white">Victoria Sterling</p>
                                        <p class="text-[10px] text-slate-400">victoria.sterling@nexusplay.demo</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 text-[10px] font-semibold">VIP Player</span>
                                        <span class="ml-1 text-[10px] text-purple-300 font-bold">Platinum</span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-emerald-400">$10,000.00</td>
                                    <td class="px-4 py-3 font-mono text-amber-300">$0.00</td>
                                    <td class="px-4 py-3 font-mono text-slate-400">$1,500.00</td>
                                    <td class="px-4 py-3 text-slate-300">High-roller crash gaming ($500 bets), claim 10% VIP weekly cashback, high-value withdrawal queue.</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-white">Marcus Vance</p>
                                        <p class="text-[10px] text-slate-400">marcus.vance@nexusplay.demo</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 text-[10px] font-semibold">Affiliate</span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-emerald-400">$1,840.00</td>
                                    <td class="px-4 py-3 font-mono text-slate-500">$0.00</td>
                                    <td class="px-4 py-3 font-mono text-slate-500">$0.00</td>
                                    <td class="px-4 py-3 text-slate-300">Inspect 6 referred player cohorts, trigger dynamic NGR recalculation, test commission payout.</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-white">Elena Rostova</p>
                                        <p class="text-[10px] text-slate-400">elena.rostova@nexusplay.demo</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 text-[10px] font-semibold">Ops Staff</span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-slate-500">N/A</td>
                                    <td class="px-4 py-3 font-mono text-slate-500">N/A</td>
                                    <td class="px-4 py-3 font-mono text-slate-500">N/A</td>
                                    <td class="px-4 py-3 text-slate-300">Review &amp; approve Victoria's $1,500 withdrawal queue, inspect payment webhook logs, adjust KYC.</td>
                                </tr>
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-white">David Sterling</p>
                                        <p class="text-[10px] text-slate-400">david.sterling@nexusplay.demo</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 text-[10px] font-semibold">Super Admin</span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-slate-500">N/A</td>
                                    <td class="px-4 py-3 font-mono text-slate-500">N/A</td>
                                    <td class="px-4 py-3 font-mono text-slate-500">N/A</td>
                                    <td class="px-4 py-3 text-slate-300">Full backoffice control, live Chart.js GGR telemetry, SHA-256 chained audit logs, CSV exports.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 4 Testing Scripts -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Step-by-Step Evaluation Workflows</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                                <p class="font-bold text-white flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-slate-800 text-emerald-400 flex items-center justify-center text-[10px] font-bold">A</span>
                                    Slot Play &amp; Rollover Wagering
                                </p>
                                <ol class="list-decimal list-inside text-slate-400 space-y-1 text-[11px] leading-relaxed">
                                    <li>Login as <span class="text-white">Alex Chen</span> &rarr; open <span class="text-emerald-400">Gates of Olympus</span>.</li>
                                    <li>Place a $5.00 bet and click <span class="text-white">Spin</span>.</li>
                                    <li>Observe cash balance update and active bonus rollover counter advance by $5.00.</li>
                                </ol>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                                <p class="font-bold text-white flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-slate-800 text-emerald-400 flex items-center justify-center text-[10px] font-bold">B</span>
                                    Cashier Sandbox &amp; Signed Webhook
                                </p>
                                <ol class="list-decimal list-inside text-slate-400 space-y-1 text-[11px] leading-relaxed">
                                    <li>Navigate to <span class="text-white">Wallet</span> &rarr; initiate $100.00 USDT deposit.</li>
                                    <li>Inspect the simulated checkout invoice and signed HMAC payload.</li>
                                    <li>Click <span class="text-emerald-400">Simulate Paid Webhook</span>; verify $100 is credited and duplicate click returns <span class="text-amber-400">409 Conflict</span>.</li>
                                </ol>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                                <p class="font-bold text-white flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-slate-800 text-emerald-400 flex items-center justify-center text-[10px] font-bold">C</span>
                                    High-Value Withdrawal Approval
                                </p>
                                <ol class="list-decimal list-inside text-slate-400 space-y-1 text-[11px] leading-relaxed">
                                    <li>Switch to <span class="text-white">Elena Rostova</span> (Ops Staff).</li>
                                    <li>Navigate to <span class="text-white">Payment Management</span>.</li>
                                    <li>Inspect Victoria's $1,500 pending withdrawal and click <span class="text-emerald-400">Approve</span>; verify locked bucket settles.</li>
                                </ol>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                                <p class="font-bold text-white flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-slate-800 text-emerald-400 flex items-center justify-center text-[10px] font-bold">D</span>
                                    Affiliate Commission Recalculation
                                </p>
                                <ol class="list-decimal list-inside text-slate-400 space-y-1 text-[11px] leading-relaxed">
                                    <li>Switch to <span class="text-white">Marcus Vance</span> (Affiliate).</li>
                                    <li>Review aggregated GGR, bonuses, and calculated NGR across 6 players.</li>
                                    <li>Click <span class="text-emerald-400">Recalculate NGR</span> and observe commission balance audit record.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Bottom Back to Top / Switch Persona CTA -->
                <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-800">
                    <button @click="scrollTo('sec-1')" class="text-xs text-slate-400 hover:text-emerald-400 flex items-center gap-1.5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                        <span>Back to top</span>
                    </button>
                    <a href="{{ route('demo.login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500 text-slate-950 font-bold text-xs hover:bg-emerald-400 transition">
                        <span>Select Demo Persona &rarr;</span>
                    </a>
                </div>

            </main>
        </div>
    </div>
</div>
@endsection
