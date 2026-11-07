<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Player Portal — NexusPlay')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col font-sans antialiased overflow-x-hidden w-full max-w-full selection:bg-emerald-500 selection:text-slate-950" x-data="{ mobileNavOpen: false, userDropdown: false }">

    <!-- Top Demo Persona & Switcher Banner -->
    @include('components.demo-banner')

    <!-- Sticky Navigation Header -->
    <header class="sticky top-0 z-40 w-full bg-slate-950/95 backdrop-blur border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-3">

                <!-- Left: Logo & Desktop Nav -->
                <div class="flex items-center gap-6">
                    <a href="{{ route('lobby.index') }}" class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-emerald-500 to-amber-500 flex items-center justify-center text-slate-950 font-black">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span class="font-extrabold text-base tracking-tight text-white hidden sm:inline">NexusPlay</span>
                    </a>

                    <!-- Nav Tabs (Desktop) -->
                    <nav class="hidden md:flex items-center gap-1 text-xs font-semibold">
                        <a href="{{ route('lobby.index') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('lobby.*') ? 'bg-slate-800 text-emerald-400' : 'text-slate-300 hover:text-white hover:bg-slate-900' }}">
                            {{ __('ui.nav.lobby') }}
                        </a>
                        <a href="{{ route('wallet.index') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('wallet.*') ? 'bg-slate-800 text-emerald-400' : 'text-slate-300 hover:text-white hover:bg-slate-900' }}">
                            {{ __('ui.nav.wallet') }}
                        </a>
                        <a href="{{ route('promotions.index') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('promotions.*') ? 'bg-slate-800 text-emerald-400' : 'text-slate-300 hover:text-white hover:bg-slate-900' }}">
                            {{ __('ui.nav.promotions') }}
                        </a>
                        <a href="{{ route('vip.index') }}" class="px-3 py-2 rounded-lg transition {{ request()->routeIs('vip.*') ? 'bg-slate-800 text-emerald-400' : 'text-slate-300 hover:text-white hover:bg-slate-900' }}">
                            {{ __('ui.nav.vip') }}
                        </a>
                    </nav>
                </div>

                <!-- Right: Sticky Wallet Balance Pill & User Actions -->
                <div class="flex items-center gap-2 sm:gap-3">
                    @php
                        $user = auth()->user();
                        $wallet = $user?->wallet;
                    @endphp

                    <!-- Balance Pill -->
                    <div class="flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs">
                        <div class="flex flex-col text-right leading-tight">
                            <span class="text-[10px] text-slate-400 font-medium">USD Cash</span>
                            <span class="font-mono font-bold text-emerald-400 text-xs sm:text-sm">
                                {{ format_usd($wallet?->cash_balance_usd ?? 0) }}
                            </span>
                        </div>

                        @if(($wallet?->bonus_balance_usd ?? 0) > 0)
                            <div class="hidden lg:flex flex-col text-right leading-tight pl-2 border-l border-slate-800">
                                <span class="text-[10px] text-amber-400 font-medium">Bonus</span>
                                <span class="font-mono font-bold text-amber-300 text-xs">
                                    {{ format_usd($wallet->bonus_balance_usd) }}
                                </span>
                            </div>
                        @endif

                        <a href="{{ route('wallet.index') }}" class="ml-1 px-2 py-1 rounded-lg bg-emerald-500 text-slate-950 font-bold hover:bg-emerald-400 transition text-[11px] sm:text-xs shrink-0">
                            + Deposit
                        </a>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="flex items-center gap-1.5 p-1 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition">
                            <div class="w-7 h-7 rounded-lg bg-slate-800 text-slate-200 flex items-center justify-center font-bold text-xs">
                                {{ substr($user?->name ?? 'P', 0, 1) }}
                            </div>
                            <svg class="w-3.5 h-3.5 text-slate-400 hidden sm:block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl py-2 z-50 text-xs">
                            <div class="px-3 py-2 border-b border-slate-800">
                                <p class="font-bold text-white truncate">{{ $user?->name }}</p>
                                <p class="text-[10px] text-slate-400 font-mono truncate">{{ $user?->email }}</p>
                            </div>
                            <a href="{{ route('profile.index') }}" class="block px-3 py-2 text-slate-300 hover:text-white hover:bg-slate-800">
                                Profile &amp; Responsible Limits
                            </a>
                            <a href="{{ route('demo.login') }}" class="block px-3 py-2 text-amber-400 hover:text-amber-300 hover:bg-slate-800">
                                Switch Demo Persona
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="pt-1 border-t border-slate-800">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 text-rose-400 hover:text-rose-300 hover:bg-slate-800">
                                    {{ __('ui.nav.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Mobile Hamburger -->
                    <button @click="mobileNavOpen = !mobileNavOpen" type="button" class="md:hidden p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800" aria-label="Toggle Navigation">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div x-show="mobileNavOpen" x-cloak class="md:hidden bg-slate-900 border-b border-slate-800 px-4 py-3 space-y-1 text-xs">
            <a @click="mobileNavOpen = false" href="{{ route('lobby.index') }}" class="block px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800 {{ request()->routeIs('lobby.*') ? 'text-emerald-400 font-bold' : '' }}">
                {{ __('ui.nav.lobby') }}
            </a>
            <a @click="mobileNavOpen = false" href="{{ route('wallet.index') }}" class="block px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800 {{ request()->routeIs('wallet.*') ? 'text-emerald-400 font-bold' : '' }}">
                {{ __('ui.nav.wallet') }}
            </a>
            <a @click="mobileNavOpen = false" href="{{ route('promotions.index') }}" class="block px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800 {{ request()->routeIs('promotions.*') ? 'text-emerald-400 font-bold' : '' }}">
                {{ __('ui.nav.promotions') }}
            </a>
            <a @click="mobileNavOpen = false" href="{{ route('vip.index') }}" class="block px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800 {{ request()->routeIs('vip.*') ? 'text-emerald-400 font-bold' : '' }}">
                {{ __('ui.nav.vip') }}
            </a>
            <a @click="mobileNavOpen = false" href="{{ route('profile.index') }}" class="block px-3 py-2 rounded-lg text-slate-200 hover:bg-slate-800">
                Profile &amp; Limits
            </a>
        </div>
    </header>

    <!-- Main Player Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Flash Notifications -->
        @if(session('status'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs sm:text-sm flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs sm:text-sm flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bottom Mobile Nav Bar (Sticky Mobile Bar on < 768px) -->
    <div class="md:hidden sticky bottom-0 z-40 bg-slate-950/95 backdrop-blur border-t border-slate-800 py-2 px-4 flex items-center justify-around text-[10px] font-semibold text-slate-400">
        <a href="{{ route('lobby.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('lobby.*') ? 'text-emerald-400' : 'hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            <span>Lobby</span>
        </a>
        <a href="{{ route('wallet.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('wallet.*') ? 'text-emerald-400' : 'hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <span>Cashier</span>
        </a>
        <a href="{{ route('promotions.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('promotions.*') ? 'text-emerald-400' : 'hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
            <span>Bonuses</span>
        </a>
        <a href="{{ route('vip.index') }}" class="flex flex-col items-center gap-1 {{ request()->routeIs('vip.*') ? 'text-emerald-400' : 'hover:text-white' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            <span>VIP</span>
        </a>
    </div>

    @stack('scripts')
</body>
</html>
