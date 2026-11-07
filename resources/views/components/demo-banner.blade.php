@if(config('demo.enabled', true))
<div class="bg-slate-900 border-b border-slate-800 text-xs text-slate-300 w-full overflow-x-hidden relative z-50">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 py-2 flex flex-col md:flex-row items-center justify-between gap-2">
        <!-- Left: Status indicator & Simulated notice -->
        <div class="flex items-center gap-2 min-w-0 w-full md:w-auto justify-between md:justify-start">
            <div class="flex items-center gap-2 min-w-0">
                <span class="relative flex h-2 w-2 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="font-medium text-emerald-400 shrink-0">DEMO MODE</span>
                <span class="text-slate-500 hidden sm:inline">•</span>
                <span class="truncate text-slate-400 text-[11px] sm:text-xs">
                    {{ __('ui.demo_mode_badge') }}
                </span>
            </div>

            <!-- Mobile language toggle inside top bar -->
            <div class="md:hidden shrink-0 flex items-center">
                <form action="{{ route('locale.switch') }}" method="POST" class="inline-flex rounded-md p-0.5 bg-slate-950 border border-slate-800">
                    @csrf
                    <input type="hidden" name="locale" value="{{ app()->getLocale() === 'en' ? 'id' : 'en' }}">
                    <button type="submit" class="px-2 py-0.5 text-[11px] font-semibold rounded text-slate-300 hover:text-white flex items-center gap-1">
                        <span class="{{ app()->getLocale() === 'en' ? 'text-emerald-400 font-bold' : 'text-slate-500' }}">EN</span>
                        <span class="text-slate-600">/</span>
                        <span class="{{ app()->getLocale() === 'id' ? 'text-emerald-400 font-bold' : 'text-slate-500' }}">ID</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Right: Role Switcher & Desktop Language Toggle -->
        <div class="flex items-center gap-2 w-full md:w-auto justify-between md:justify-end">
            @auth
                <!-- Active persona pill -->
                <div class="hidden lg:flex items-center gap-1.5 px-2 py-1 rounded bg-slate-800/80 text-slate-300 text-[11px] shrink-0 border border-slate-700/50">
                    <span class="text-slate-400">{{ __('ui.nav.current_role') }}:</span>
                    <span class="font-semibold text-emerald-400">{{ auth()->user()->name }}</span>
                    <span class="text-slate-500">({{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }})</span>
                </div>
            @endauth

            <!-- Persona Switcher: Mobile Select Dropdown (< 768px) -->
            <div class="md:hidden w-full min-w-0">
                <form action="{{ route('demo.quick-login') }}" method="POST" class="flex items-center gap-1.5 w-full">
                    @csrf
                    <select name="persona_key" onchange="this.form.submit()" class="w-full bg-slate-950 border border-slate-700 rounded px-2 py-1 text-xs text-slate-200 focus:outline-none focus:border-emerald-500">
                        <option value="" disabled selected>{{ __('ui.nav.role_switcher') }}...</option>
                        @foreach(config('demo.personas', []) as $pKey => $p)
                            <option value="{{ $pKey }}" @if(auth()->check() && (session('demo_persona_key') === $pKey || auth()->user()->persona_key === $pKey)) selected @endif>
                                {{ $p['name'] }} ({{ app()->getLocale() === 'id' ? $p['badge_id'] : $p['badge_en'] }})
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <!-- Persona Switcher: Desktop Quick Buttons (>= 768px) -->
            <div class="hidden md:flex items-center gap-1 shrink-0">
                <span class="text-[11px] text-slate-400 mr-1">{{ __('ui.nav.role_switcher') }}:</span>
                @foreach(config('demo.personas', []) as $pKey => $p)
                    @php
                        $isActive = auth()->check() && (session('demo_persona_key') === $pKey || auth()->user()->persona_key === $pKey);
                    @endphp
                    <form action="{{ route('demo.quick-login') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="persona_key" value="{{ $pKey }}">
                        <button type="submit" 
                                title="{{ $p['name'] }} - {{ app()->getLocale() === 'id' ? $p['badge_id'] : $p['badge_en'] }}"
                                class="px-2 py-1 text-[11px] font-medium rounded transition-colors {{ $isActive ? 'bg-emerald-500 text-slate-950 font-bold shadow-sm' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                            {{ explode(' ', $p['name'])[0] }}
                            <span class="opacity-70 text-[10px]">
                                @if($p['role'] === 'super_admin') (Admin)
                                @elseif($p['role'] === 'ops_staff') (Staff)
                                @elseif($p['role'] === 'affiliate') (Aff)
                                @elseif($pKey === 'player_vip') (VIP)
                                @else (Std)
                                @endif
                            </span>
                        </button>
                    </form>
                @endforeach
            </div>

            <!-- Desktop Language Switcher -->
            <div class="hidden md:flex items-center shrink-0 border-l border-slate-800 pl-2">
                <form action="{{ route('locale.switch') }}" method="POST" class="inline-flex rounded-md p-0.5 bg-slate-950 border border-slate-800">
                    @csrf
                    <input type="hidden" name="locale" value="{{ app()->getLocale() === 'en' ? 'id' : 'en' }}">
                    <button type="submit" class="px-2 py-0.5 text-xs font-semibold rounded text-slate-300 hover:text-white flex items-center gap-1" title="Toggle English / Indonesian">
                        <span class="{{ app()->getLocale() === 'en' ? 'text-emerald-400 font-bold' : 'text-slate-500' }}">EN</span>
                        <span class="text-slate-600">/</span>
                        <span class="{{ app()->getLocale() === 'id' ? 'text-emerald-400 font-bold' : 'text-slate-500' }}">ID</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
