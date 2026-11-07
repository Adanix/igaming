@extends('layouts.public')

@section('title', 'Select Demo Persona — NexusPlay iGaming Turnkey Demo')

@section('content')
<div class="bg-slate-950 text-slate-100 min-h-screen py-10" x-data="{ resetModalOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

        <!-- Header & Instructions -->
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 pb-6 border-b border-slate-800">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Zero-Friction Authentication Sandbox</span>
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight">
                    Select a Demo Persona
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 max-w-2xl leading-relaxed">
                    No sign-up or passwords required. Select any role below to instantly authenticate and evaluate player gameplay, cashier transactions, affiliate commissions, or data-dense ops backoffice.
                </p>
            </div>

            <!-- Action Toolbar (Anti-overflow flex-wrap) -->
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <button @click="resetModalOpen = true" type="button" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-xl bg-slate-900 text-rose-300 border border-rose-500/30 hover:bg-rose-500/10 transition shadow-sm">
                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Reset Demo Database</span>
                </button>
                <a href="{{ route('public.docs') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-xl bg-slate-800 text-slate-200 border border-slate-700 hover:bg-slate-700 transition">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>View Docs</span>
                </a>
            </div>
        </div>

        <!-- Success Status Notice -->
        @if(session('status'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs sm:text-sm flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <!-- 5 Persona Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($personas as $key => $item)
                @php
                    $config = $item['config'];
                    $user = $item['user'];
                    $wallet = $item['wallet'];

                    // Color theme mappings
                    $theme = match($key) {
                        'player_standard' => ['border' => 'border-blue-500/30', 'bg' => 'from-blue-950/20', 'badge' => 'bg-blue-500/20 text-blue-300', 'btn' => 'from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500'],
                        'player_vip' => ['border' => 'border-purple-500/30', 'bg' => 'from-purple-950/20', 'badge' => 'bg-purple-500/20 text-purple-300', 'btn' => 'from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500'],
                        'affiliate_partner' => ['border' => 'border-emerald-500/30', 'bg' => 'from-emerald-950/20', 'badge' => 'bg-emerald-500/20 text-emerald-300', 'btn' => 'from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500'],
                        'ops_finance_staff' => ['border' => 'border-amber-500/30', 'bg' => 'from-amber-950/20', 'badge' => 'bg-amber-500/20 text-amber-300', 'btn' => 'from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500'],
                        'super_admin' => ['border' => 'border-rose-500/30', 'bg' => 'from-rose-950/20', 'badge' => 'bg-rose-500/20 text-rose-300', 'btn' => 'from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500'],
                        default => ['border' => 'border-slate-800', 'bg' => 'from-slate-900', 'badge' => 'bg-slate-800 text-slate-300', 'btn' => 'from-emerald-600 to-emerald-500'],
                    };

                    $isAdmin = in_array($user?->role, ['ops_staff', 'super_admin'], true);
                @endphp

                <div class="relative flex flex-col justify-between rounded-2xl bg-gradient-to-b {{ $theme['bg'] }} to-slate-900/90 border {{ $theme['border'] }} p-6 shadow-xl hover:border-slate-600 transition-all duration-200">

                    <!-- Top Card Info -->
                    <div class="space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $theme['badge'] }}">
                                    {{ strtoupper(str_replace('_', ' ', $user?->role ?? $config['role'])) }}
                                </span>
                                @if($user?->vipTier)
                                    <span class="inline-block ml-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                        {{ $user->vipTier->name }} Tier
                                    </span>
                                @endif
                                <h3 class="text-lg font-bold text-white mt-1.5">{{ $config['name'] }}</h3>
                                <p class="text-xs text-slate-400 font-mono">{{ $config['email'] }}</p>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 font-bold shrink-0">
                                {{ substr($config['name'], 0, 1) }}
                            </div>
                        </div>

                        <p class="text-xs text-slate-300 leading-relaxed">
                            {{ app()->getLocale() === 'id' ? ($config['summary_id'] ?? $config['summary_en'] ?? '') : ($config['summary_en'] ?? '') }}
                        </p>

                        <!-- Live Starting Balances (PRD Section 22.2) -->
                        @if($wallet)
                            <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800/80 space-y-1.5 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Cash Balance:</span>
                                    <span class="font-mono font-bold text-emerald-400">{{ format_usd($wallet->cash_balance_usd) }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Bonus Balance:</span>
                                    <span class="font-mono font-semibold text-amber-300">{{ format_usd($wallet->bonus_balance_usd) }}</span>
                                </div>
                                @if($wallet->locked_withdrawal_usd > 0)
                                    <div class="flex items-center justify-between text-slate-400">
                                        <span>Locked Withdrawal:</span>
                                        <span class="font-mono text-slate-300">{{ format_usd($wallet->locked_withdrawal_usd) }}</span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800/80 text-xs text-slate-400 flex items-center justify-between">
                                <span>Platform Access:</span>
                                <span class="font-mono text-emerald-400 font-semibold">Staff &amp; Executive Privileges</span>
                            </div>
                        @endif

                        <!-- Capability Bullets -->
                        @php
                            $caps = $config['capabilities'] ?? [
                                'player_standard' => ['Play Video Slots & Crash', 'Accumulate Bonus Rollover', 'Request Standard Withdrawal'],
                                'player_vip' => ['High-Roller Bets & Multipliers', 'Claim Weekly 12% Cashback', 'VIP High-Value Cashier Queue'],
                                'affiliate_partner' => ['Track 6+ Referred Players', 'Recalculate NGR & 30% RevShare', 'Request Crypto/Wire Payouts'],
                                'ops_finance_staff' => ['Approve High-Value Payouts', 'Inspect Signed Webhook Telemetry', 'KYC & Player Status Adjustments'],
                                'super_admin' => ['Live Chart.js KPI Telemetry', 'Immutable SHA-256 Audit Trail', 'Supervised Balance Adjustments'],
                            ][$key] ?? [];
                        @endphp
                        <div class="space-y-1.5 pt-1">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Core Capabilities</p>
                            <ul class="text-xs text-slate-300 space-y-1">
                                @foreach($caps as $cap)
                                    <li class="flex items-start gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="leading-tight">{{ $cap }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- Single-Click Login Action Form -->
                    <form method="POST" action="{{ route('demo.quick-login') }}" class="pt-6 mt-4 border-t border-slate-800/80 space-y-3">
                        @csrf
                        <input type="hidden" name="persona_key" value="{{ $key }}">

                        @if($isAdmin)
                            <!-- MFA Bypass Option (PRD Section 22.2) -->
                            <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer select-none">
                                <input type="checkbox" name="skip_mfa" value="1" checked class="rounded border-slate-700 bg-slate-900 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-slate-950">
                                <span>Bypass MFA challenge (or uncheck to test TOTP)</span>
                            </label>
                        @endif

                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-gradient-to-r {{ $theme['btn'] }} text-white font-bold text-xs sm:text-sm shadow-lg transition transform hover:-translate-y-0.5">
                            <span>Login as {{ explode(' ', $config['name'])[0] }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

    </div>

    <!-- Platform Reset Confirmation Modal (Alpine.js) -->
    <div x-show="resetModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div @click="resetModalOpen = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Panel -->
            <div class="inline-block align-bottom bg-slate-900 rounded-2xl border border-slate-800 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6 space-y-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white" id="modal-title">Reset Demo Database?</h3>
                        <p class="text-xs text-slate-400">Restore all wallets, game rounds, and webhooks to seed state.</p>
                    </div>
                </div>

                <div class="text-xs text-slate-300 leading-relaxed space-y-2">
                    <p>This action will completely wipe all newly created game rounds, deposit invoices, withdrawal requests, and audit logs, re-seeding the 5 canonical personas with exact initial balances ($250, $10,000, $1,840, etc.).</p>
                    <p class="text-amber-400 font-semibold">Any test progress will be reset.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button @click="resetModalOpen = false" type="button" class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-800 text-slate-300 hover:bg-slate-700 transition">
                        Cancel
                    </button>
                    <form method="POST" action="{{ route('demo.reset') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-xl bg-rose-600 text-white hover:bg-rose-500 transition shadow-lg shadow-rose-600/30">
                            Confirm Database Reset
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
