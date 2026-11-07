@extends('layouts.player')

@section('title', 'Payment Gateway Sandbox — Invoice ' . $transaction->reference)

@section('content')
<div class="space-y-6 max-w-4xl mx-auto" x-data="{
    loading: false,
    responseResult: null,
    sendWebhook(outcome) {
        this.loading = true;
        this.responseResult = null;

        const payload = {{ json_encode($sandboxData['payload']) }};
        payload.outcome = outcome;
        if (outcome === 'duplicate_replay') {
            payload.outcome = 'duplicate_replay';
        }

        fetch('{{ route('webhooks.payment.callback') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Signature': '{{ $sandboxData['signature'] }}'
            },
            body: JSON.stringify({
                payload: payload,
                signature: '{{ $sandboxData['signature'] }}',
                outcome: payload.outcome,
                reference: '{{ $transaction->reference }}',
                idempotency_key: '{{ $sandboxData['idempotency_key'] }}'
            })
        })
        .then(async res => {
            const data = await res.json();
            this.loading = false;
            this.responseResult = {
                status: res.status,
                ok: res.ok,
                data: data
            };
        })
        .catch(err => {
            this.loading = false;
            this.responseResult = {
                status: 500,
                ok: false,
                data: { error: err.message }
            };
        });
    }
}">

    <!-- Breadcrumb -->
    <div class="flex items-center gap-3">
        <a href="{{ route('wallet.index') }}" class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white">Payment Gateway Simulator Sandbox</h1>
            <p class="text-xs text-slate-400">Invoice: <span class="font-mono text-emerald-400">{{ $transaction->reference }}</span></p>
        </div>
    </div>

    <!-- Sandbox Invoice Card -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">

        <!-- Invoice Details & Controls (7 cols) -->
        <div class="md:col-span-7 space-y-5">
            <div class="p-6 rounded-3xl bg-slate-900 border border-slate-800 space-y-5">
                <div class="flex items-start justify-between pb-4 border-b border-slate-800">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Simulated Gateway Checkout</span>
                        <h2 class="text-lg font-bold text-white mt-0.5">{{ $transaction->paymentMethod?->name ?? 'USD Gateway' }}</h2>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-black font-mono text-emerald-400">{{ format_usd($transaction->amount_usd) }}</span>
                        <span class="block text-[10px] text-slate-400 uppercase font-mono">{{ $transaction->status }}</span>
                    </div>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-800/60">
                        <span class="text-slate-400">Merchant Reference:</span>
                        <span class="font-mono text-slate-200">{{ $transaction->reference }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-800/60">
                        <span class="text-slate-400">Created At:</span>
                        <span class="text-slate-200">{{ $transaction->created_at->format('Y-m-d H:i:s') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-800/60">
                        <span class="text-slate-400">Idempotency Key:</span>
                        <span class="font-mono text-slate-300 text-[11px] truncate max-w-xs">{{ $sandboxData['idempotency_key'] }}</span>
                    </div>
                </div>

                <!-- Simulation Trigger Buttons -->
                <div class="space-y-3 pt-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-300">Trigger Webhook Callbacks</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <button @click="sendWebhook('paid')" type="button" class="py-2.5 px-3 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-400 text-slate-950 font-bold text-xs hover:from-emerald-400 transition shadow-md shadow-emerald-500/20 text-center">
                            &check; Simulate Paid (Success)
                        </button>
                        <button @click="sendWebhook('duplicate_replay')" type="button" class="py-2.5 px-3 rounded-xl bg-amber-500/20 border border-amber-500/40 text-amber-300 font-bold text-xs hover:bg-amber-500/30 transition text-center">
                            &circlearrowright; Simulate Replay Attack
                        </button>
                        <button @click="sendWebhook('failed')" type="button" class="py-2.5 px-3 rounded-xl bg-rose-500/20 border border-rose-500/40 text-rose-300 font-bold text-xs hover:bg-rose-500/30 transition text-center">
                            &times; Simulate Failed
                        </button>
                        <button @click="sendWebhook('expired')" type="button" class="py-2.5 px-3 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 font-bold text-xs hover:bg-slate-700 transition text-center">
                            &bull; Simulate Expired
                        </button>
                    </div>
                </div>

                <div class="pt-2 flex justify-between items-center text-xs">
                    <a href="{{ route('wallet.index') }}" class="text-emerald-400 hover:underline font-semibold">&larr; Return to Cashier</a>
                    <span class="text-[11px] text-slate-500">Live webhook simulator active</span>
                </div>
            </div>

            <!-- Webhook Response Telemetry Viewer -->
            <div x-show="responseResult" x-cloak class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-white">Callback Execution Telemetry</span>
                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded" :class="responseResult && responseResult.ok ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400'">
                        HTTP <span x-text="responseResult ? responseResult.status : ''"></span>
                    </span>
                </div>

                <pre class="p-3 rounded-xl bg-slate-950 font-mono text-[11px] text-slate-300 overflow-x-auto max-h-52" x-text="responseResult ? JSON.stringify(responseResult.data, null, 2) : ''"></pre>

                <div class="flex justify-end">
                    <a href="{{ route('wallet.index') }}" class="px-4 py-2 rounded-xl bg-emerald-500 text-slate-950 font-bold text-xs hover:bg-emerald-400 transition">
                        View Updated Wallet &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Right: Signed Security Header & Payload Details (5 cols) -->
        <div class="md:col-span-5 space-y-4">
            <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 space-y-3 text-xs">
                <div class="flex items-center gap-2 text-emerald-400 font-bold uppercase tracking-wider text-[11px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>HMAC-SHA256 Signing Spec</span>
                </div>

                <p class="text-slate-400 text-[11px] leading-relaxed">
                    Every webhook callback transmits an <code class="text-emerald-400">X-Signature</code> header generated using the platform secret:
                </p>

                <div class="p-2.5 rounded-lg bg-slate-950 font-mono text-[10px] text-slate-300 break-all select-all border border-slate-800">
                    <span class="text-slate-500">Header:</span> X-Signature<br>
                    <span class="text-slate-500">Value:</span> {{ $sandboxData['signature'] }}
                </div>

                <div>
                    <p class="text-[10px] text-slate-400 uppercase font-semibold mb-1">Raw Payload JSON</p>
                    <pre class="p-2.5 rounded-lg bg-slate-950 font-mono text-[10px] text-slate-300 overflow-x-auto max-h-48 border border-slate-800">{{ json_encode($sandboxData['payload'], JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
