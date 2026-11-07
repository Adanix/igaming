@extends('layouts.admin')

@section('title', 'Global Platform Settings — NexusPlay Backoffice')

@section('content')
<div class="max-w-4xl space-y-6">

    <!-- Header -->
    <div class="pb-4 border-b border-slate-800">
        <h1 class="text-2xl font-black text-white">Global Platform Configuration</h1>
        <p class="text-xs text-slate-400">Manage risk thresholds, revenue share admin fee percentage, and base monetary parameters</p>
    </div>

    <!-- Settings Form -->
    <div class="p-6 sm:p-8 rounded-3xl bg-slate-900 border border-slate-800 space-y-6">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6 text-xs">
            @csrf

            <div class="space-y-4">
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider">Financial &amp; Cashier Thresholds</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block font-bold text-slate-300">Base Currency Code</label>
                        <input type="text" readonly value="{{ $settings['base_currency']?->value ?? 'USD' }}" class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-4 py-2.5 text-slate-400 font-mono cursor-not-allowed">
                        <p class="text-[10px] text-slate-500">Immutable ledger standard currency across all accounts.</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block font-bold text-slate-300">High-Value Withdrawal Threshold (USD)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2.5 text-slate-400 font-bold">$</span>
                            <input type="number" step="50" name="settings[high_value_withdrawal_threshold_usd]" value="{{ $settings['high_value_withdrawal_threshold_usd']?->value ?? '1000.00' }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl pl-7 pr-3 py-2 text-white font-mono font-bold focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                        <p class="text-[10px] text-slate-400">Withdrawals equal to or exceeding this threshold require Ops Staff review.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4 pt-4 border-t border-slate-800">
                <h3 class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Affiliate &amp; Gaming Rules</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block font-bold text-slate-300">Platform Admin Fee Percentage (%)</label>
                        <input type="number" step="0.5" min="0" max="50" name="settings[affiliate_platform_admin_fee_pct]" value="{{ $settings['affiliate_platform_admin_fee_pct']?->value ?? '15.00' }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <p class="text-[10px] text-slate-400">Deducted from GGR before calculating Net Gaming Revenue (NGR).</p>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block font-bold text-slate-300">Default Game Engine RTP (%)</label>
                        <input type="number" step="0.1" min="80" max="99" name="settings[default_game_rtp_pct]" value="{{ $settings['default_game_rtp_pct']?->value ?? '96.50' }}" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-white font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <p class="text-[10px] text-slate-400">Standard Return-to-Player percentage for unconfigured games.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4 pt-4 border-t border-slate-800">
                <h3 class="text-xs font-bold text-rose-400 uppercase tracking-wider">Demo Environment Status</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block font-bold text-slate-300">Demo Persona Sandbox Mode</label>
                        <select name="settings[demo_mode_active]" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white">
                            <option value="true" {{ ($settings['demo_mode_active']?->value ?? 'true') === 'true' ? 'selected' : '' }}>Enabled (Allow 1-click persona switching)</option>
                            <option value="false" {{ ($settings['demo_mode_active']?->value ?? 'true') === 'false' ? 'selected' : '' }}>Disabled (Strict production credentials)</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block font-bold text-slate-300">Platform Maintenance Mode</label>
                        <select name="settings[maintenance_mode]" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white">
                            <option value="false" {{ ($settings['maintenance_mode']?->value ?? 'false') === 'false' ? 'selected' : '' }}>Online (Active Service)</option>
                            <option value="true" {{ ($settings['maintenance_mode']?->value ?? 'false') === 'true' ? 'selected' : '' }}>Maintenance Window (Maintenance banner)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-bold hover:from-amber-400 transition shadow-lg shadow-amber-500/20">
                    Save System Settings &amp; Sign Audit
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
