@extends('layouts.admin')

@section('title', 'Tamper-Evident Audit Trail & Webhook Telemetry — NexusPlay Backoffice')

@section('content')
<div class="space-y-6" x-data="{
    activeTab: '{{ $tab }}',
    payloadModal: false,
    selectedLog: null,
    inspectLog(log) {
        this.selectedLog = log;
        this.payloadModal = true;
    }
}">

    <!-- Header & Integrity Badge -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white">Immutable Audit Trail &amp; Webhook Telemetry</h1>
            <p class="text-xs text-slate-400">Cryptographically chained ledger events and external webhook verification logs</p>
        </div>

        <div>
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl {{ $isChainIntact ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400' : 'bg-rose-500/10 border border-rose-500/30 text-rose-400' }} text-xs font-bold">
                <span class="w-2 h-2 rounded-full {{ $isChainIntact ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }}"></span>
                <span>{{ $isChainIntact ? 'SHA-256 Hash Chain Verified Intact' : 'Integrity Mismatch Detected' }}</span>
            </span>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex items-center gap-3 border-b border-slate-800 pb-3">
        <button @click="activeTab = 'audit'" type="button"
                :class="activeTab === 'audit' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800'"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition">
            Cryptographic Audit Trail ({{ $auditLogs->total() }})
        </button>
        <button @click="activeTab = 'webhook'" type="button"
                :class="activeTab === 'webhook' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-800'"
                class="px-4 py-2 rounded-xl text-xs font-semibold transition">
            Simulated Webhook Logs ({{ $webhookLogs->total() }})
        </button>
    </div>

    <!-- Tab 1: Cryptographic Audit Trail -->
    <div x-show="activeTab === 'audit'" class="space-y-4">
        <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
            <table class="w-full text-left text-xs text-slate-300 font-mono">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800 font-sans">
                    <tr>
                        <th class="px-4 py-3">Timestamp</th>
                        <th class="px-4 py-3">Actor</th>
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">Summary</th>
                        <th class="px-4 py-3">Previous Hash</th>
                        <th class="px-4 py-3">Integrity Hash</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($auditLogs as $a)
                        <tr>
                            <td class="px-4 py-3 text-slate-400 font-sans text-[11px]">{{ $a->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3 font-sans font-bold text-white">{{ $a->user?->name ?? 'System Worker' }}</td>
                            <td class="px-4 py-3 text-amber-400 font-bold">{{ $a->action }}</td>
                            <td class="px-4 py-3 font-sans text-xs text-slate-300 max-w-xs truncate">{{ $a->summary }}</td>
                            <td class="px-4 py-3 text-slate-500 text-[10px]">
                                {{ $a->previous_hash ? substr($a->previous_hash, 0, 10) . '...' : 'GENESIS' }}
                            </td>
                            <td class="px-4 py-3 text-emerald-400 text-[10px] font-bold">
                                {{ substr($a->integrity_hash, 0, 14) }}...
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500 font-sans">No audit events found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $auditLogs->links() }}</div>
    </div>

    <!-- Tab 2: Simulated Webhook Logs -->
    <div x-show="activeTab === 'webhook'" x-cloak class="space-y-4">
        <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
            <table class="w-full text-left text-xs text-slate-300 font-mono">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800 font-sans">
                    <tr>
                        <th class="px-4 py-3">Timestamp</th>
                        <th class="px-4 py-3">Source</th>
                        <th class="px-4 py-3">Event</th>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Signature Valid</th>
                        <th class="px-4 py-3">Outcome</th>
                        <th class="px-4 py-3 text-right">Payload</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($webhookLogs as $w)
                        <tr>
                            <td class="px-4 py-3 text-slate-400 font-sans text-[11px]">{{ $w->created_at->format('Y-m-d H:i:s') }}</td>
                            <td class="px-4 py-3 font-sans uppercase font-bold text-[10px] text-slate-300">{{ str_replace('_', ' ', $w->source_type) }}</td>
                            <td class="px-4 py-3 text-white font-bold">{{ $w->event_name }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $w->reference }}</td>
                            <td class="px-4 py-3 font-sans">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $w->signature_valid ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                    {{ $w->signature_valid ? 'Valid (HMAC)' : 'Invalid' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-sans">
                                <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold {{ $w->processing_outcome === 'processed' ? 'bg-emerald-500/20 text-emerald-400' : ($w->processing_outcome === 'duplicate_replay_blocked' ? 'bg-amber-500/20 text-amber-300' : 'bg-rose-500/20 text-rose-400') }}">
                                    {{ str_replace('_', ' ', $w->processing_outcome) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-sans">
                                <button @click="inspectLog({{ json_encode($w) }})" type="button" class="px-2.5 py-1 rounded-lg bg-slate-800 text-emerald-400 hover:text-emerald-300 text-[11px] font-semibold border border-slate-700">
                                    JSON
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500 font-sans">No webhook telemetry recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $webhookLogs->links() }}</div>
    </div>

    <!-- Payload Inspector Modal -->
    <div x-show="payloadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div @click="payloadModal = false" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-slate-900 rounded-2xl border border-slate-800 text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white">Simulated Webhook Payload Inspection</h3>
                    <button @click="payloadModal = false" class="text-slate-400 hover:text-white">&times;</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400">HMAC-SHA256 Signature Header</span>
                        <div class="p-2.5 rounded-xl bg-slate-950 font-mono text-[10px] text-emerald-400 break-all select-all border border-slate-800" x-text="selectedLog ? selectedLog.signature_header : ''"></div>
                    </div>

                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400">Request Body Payload</span>
                        <pre class="p-3 rounded-xl bg-slate-950 font-mono text-[11px] text-slate-300 overflow-x-auto max-h-56 border border-slate-800" x-text="selectedLog ? JSON.stringify(selectedLog.payload_json, null, 2) : ''"></pre>
                    </div>

                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400">Controller Response JSON</span>
                        <pre class="p-3 rounded-xl bg-slate-950 font-mono text-[11px] text-slate-300 overflow-x-auto max-h-36 border border-slate-800" x-text="selectedLog ? JSON.stringify(selectedLog.response_json, null, 2) : ''"></pre>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button @click="payloadModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-200 text-xs">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
