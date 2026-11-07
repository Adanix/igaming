<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NexusPlay — Turnkey iGaming Platform & Backoffice Demo')</title>

    <meta name="description" content="Turnkey iGaming Platform, Seamless USD Wallet Engine, and Backoffice Demo built on Laravel 13, Tailwind CSS 4, and Preline UI.">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col font-sans antialiased overflow-x-hidden w-full max-w-full selection:bg-emerald-500 selection:text-slate-950" x-data="{ mobileMenuOpen: false }">

    <!-- Top Demo Persona & Language Banner -->
    @include('components.demo-banner')

    <!-- Main Public Header -->
    <header class="sticky top-0 z-40 w-full bg-slate-950/90 backdrop-blur-md border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Logo -->
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('public.home') }}" class="flex items-center gap-2.5 text-slate-100 hover:text-white transition">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-amber-500 flex items-center justify-center shadow-lg shadow-emerald-500/20 text-slate-950 font-black text-lg">
                            <svg class="w-5 h-5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-extrabold text-lg tracking-tight bg-gradient-to-r from-white via-slate-100 to-slate-300 bg-clip-text text-transparent">NexusPlay</span>
                            <span class="text-[10px] text-emerald-400 font-medium uppercase tracking-wider -mt-1">Turnkey Platform</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Nav Links -->
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-300">
                    <a href="{{ route('public.home') }}#features" class="hover:text-white transition">{{ __('ui.nav.features') }}</a>
                    <a href="{{ route('public.home') }}#games" class="hover:text-white transition">{{ __('ui.nav.games') }}</a>
                    <a href="{{ route('public.home') }}#architecture" class="hover:text-white transition">Architecture</a>
                    <a href="{{ route('public.docs') }}" class="hover:text-white transition flex items-center gap-1.5 {{ request()->routeIs('public.docs') ? 'text-emerald-400 font-semibold' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('ui.nav.docs') }}
                    </a>
                </nav>

                <!-- Right Actions -->
                <div class="flex items-center gap-3">
                    @auth
                        @php
                            $dashboardRoute = auth()->user()->isPlayer() ? 'lobby.index' : (auth()->user()->isAffiliate() ? 'affiliate.dashboard' : 'admin.dashboard');
                        @endphp
                        <a href="{{ route($dashboardRoute) }}" class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 text-slate-200 hover:bg-slate-700 hover:text-white border border-slate-700 transition">
                            <span>Open {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</span>
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    @endauth

                    <a href="{{ route('demo.login') }}" class="inline-flex items-center gap-2 px-4 py-2 text-xs sm:text-sm font-semibold rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-400 text-slate-950 hover:from-emerald-400 hover:to-emerald-300 shadow-md shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5">
                        <span>{{ __('ui.nav.explore_demo') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>

                    <!-- Mobile Hamburger Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500" aria-label="Toggle Navigation">
                        <svg x-show="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden bg-slate-900 border-b border-slate-800 px-4 pt-3 pb-5 space-y-2">
            <a @click="mobileMenuOpen = false" href="{{ route('public.home') }}#features" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-200 hover:bg-slate-800">{{ __('ui.nav.features') }}</a>
            <a @click="mobileMenuOpen = false" href="{{ route('public.home') }}#games" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-200 hover:bg-slate-800">{{ __('ui.nav.games') }}</a>
            <a @click="mobileMenuOpen = false" href="{{ route('public.home') }}#architecture" class="block px-3 py-2 rounded-lg text-base font-medium text-slate-200 hover:bg-slate-800">Architecture</a>
            <a @click="mobileMenuOpen = false" href="{{ route('public.docs') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-emerald-400 hover:bg-slate-800">{{ __('ui.nav.docs') }}</a>
            <div class="pt-3 border-t border-slate-800 flex flex-col gap-2">
                <a href="{{ route('demo.login') }}" class="w-full text-center py-2.5 px-4 rounded-xl bg-emerald-500 text-slate-950 font-semibold text-sm">
                    {{ __('ui.nav.explore_demo') }}
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Slot -->
    <main class="flex-1 w-full max-w-full">
        @yield('content')
    </main>

    <!-- Public Footer -->
    <footer class="bg-slate-950 border-t border-slate-800/80 text-slate-400 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Col 1: Brand & Notice -->
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-amber-500 flex items-center justify-center font-bold text-slate-950 text-sm">
                            NP
                        </div>
                        <span class="font-bold text-base text-white">NexusPlay iGaming</span>
                    </div>
                    <p class="text-xs text-slate-400 max-w-md leading-relaxed">
                        {{ __('ui.tagline') }}. Designed for demonstration, technical evaluation, and rapid turnkey white-label deployment in USD ($).
                    </p>
                    <div class="pt-2">
                        <a href="mailto:{{ config('demo.contact_email') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-400 hover:text-emerald-300 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <span>{{ config('demo.contact_email') }}</span>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Navigation Links -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider">Navigation</h4>
                    <ul class="space-y-1.5 text-xs">
                        <li><a href="{{ route('public.home') }}" class="hover:text-emerald-400 transition">{{ __('ui.nav.home') }}</a></li>
                        <li><a href="{{ route('public.docs') }}" class="hover:text-emerald-400 transition">{{ __('ui.nav.docs') }}</a></li>
                        <li><a href="{{ route('demo.login') }}" class="hover:text-emerald-400 transition">{{ __('ui.nav.explore_demo') }}</a></li>
                    </ul>
                </div>

                <!-- Col 3: Modules & Tech -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider">Architecture</h4>
                    <ul class="space-y-1.5 text-xs">
                        <li><span class="text-slate-500">Currency:</span> USD ($) DECIMAL(18,2)</li>
                        <li><span class="text-slate-500">Framework:</span> Laravel 13 + PHP 8.5</li>
                        <li><span class="text-slate-500">Security:</span> OWASP ASVS L2/L3</li>
                        <li><span class="text-slate-500">UI Stack:</span> Preline + Tailwind 4 + Alpine</li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} NexusPlay Platform. All rights reserved.</p>
                <p class="flex items-center gap-1">
                    <span>Direct Inquiries:</span>
                    <a href="mailto:{{ config('demo.contact_email') }}" class="text-emerald-400 hover:underline font-mono">{{ config('demo.contact_email') }}</a>
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
