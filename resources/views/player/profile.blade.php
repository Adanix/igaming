@extends('layouts.player')

@section('title', 'Profile & Responsible Gaming — NexusPlay')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">

    <!-- Header -->
    <div class="pb-4 border-b border-slate-800">
        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Player Profile &amp; Responsible Gaming</h1>
        <p class="text-xs sm:text-sm text-slate-400 mt-1">
            Manage your account details, verification credentials, and daily USD deposit thresholds.
        </p>
    </div>

    <!-- Profile & Limits Form -->
    <div class="p-6 sm:p-8 rounded-3xl bg-slate-900 border border-slate-800 space-y-6">
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf

            <!-- KYC Badge -->
            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400">KYC Verification Status</span>
                    <p class="text-sm font-bold text-white mt-0.5">Identity Verification</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $user->isKycVerified() ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : ($user->kyc_status === 'pending' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-slate-800 text-slate-400') }}">
                    {{ $user->kyc_status ?? 'unverified' }}
                </span>
            </div>

            <!-- Profile Info Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Email Address</label>
                    <input type="email" value="{{ $user->email }}" disabled class="w-full bg-slate-950/60 border border-slate-800/80 rounded-xl px-4 py-2.5 text-xs text-slate-500 cursor-not-allowed">
                </div>
            </div>

            <!-- Responsible Gaming: Daily Limit -->
            <div class="space-y-2 pt-2 border-t border-slate-800">
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Daily Deposit Limit (USD)</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-xs font-bold text-slate-400">$</span>
                    <input type="number" step="10" name="daily_deposit_limit_usd" value="{{ old('daily_deposit_limit_usd', $user->daily_deposit_limit_usd) }}" placeholder="e.g. 500.00 (leave blank for unlimited)" class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-8 pr-4 py-2.5 text-xs font-mono text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <p class="text-[11px] text-slate-400">
                    Self-imposed ceiling. Once reached within a 24-hour cycle, further deposit invoices cannot be initiated.
                </p>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-400 text-slate-950 font-bold text-xs sm:text-sm hover:from-emerald-400 transition shadow-md shadow-emerald-500/20">
                    Save Profile Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
