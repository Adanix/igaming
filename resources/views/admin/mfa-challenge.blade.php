@extends('layouts.public')

@section('title', 'Admin Multi-Factor Authentication Challenge — NexusPlay')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6 p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 mx-auto flex items-center justify-center font-bold text-xl">
                🔐
            </div>
            <h1 class="text-2xl font-black text-white">Backoffice Security Challenge</h1>
            <p class="text-xs text-slate-400 leading-relaxed">
                Ops Staff and Super Admin accounts require session-bound Multi-Factor Authentication (TOTP).
            </p>
        </div>

        <!-- Demo Hint Banner -->
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs space-y-1">
            <p class="font-bold">Simulated TOTP Code Available</p>
            <p class="text-[11px] text-slate-300">For instant demo verification, use code <strong class="text-emerald-400 font-mono text-sm underline">123456</strong>.</p>
        </div>

        @if($errors->any())
            <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.mfa.verify') }}" class="space-y-4">
            @csrf
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">6-Digit TOTP Code</label>
                <input type="text" name="code" value="123456" maxlength="6" autofocus required class="w-full text-center tracking-[0.5em] font-mono text-2xl font-black bg-slate-950 border border-slate-800 rounded-xl py-3 text-white focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-bold text-xs sm:text-sm hover:from-amber-400 transition shadow-lg shadow-amber-500/20">
                Verify MFA &amp; Enter Backoffice &rarr;
            </button>
        </form>

        <div class="pt-2 text-center text-xs">
            <a href="{{ route('demo.login') }}" class="text-slate-400 hover:text-white transition">
                &larr; Switch Demo Persona
            </a>
        </div>
    </div>
</div>
@endsection
