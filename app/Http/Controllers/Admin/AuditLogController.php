<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SimulatedWebhookLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display Immutable SHA-256 Chained Audit Trail & Webhook Telemetry.
     */
    public function index(Request $request): View
    {
        $action = $request->query('action');
        $tab = $request->query('tab', 'audit');

        $auditQuery = AuditLog::with('user');

        if ($action) {
            $auditQuery->where('action', 'like', "%{$action}%");
        }

        $auditLogs = $auditQuery->latest('id')->paginate(20, ['*'], 'audit_page')->withQueryString();

        $webhookLogs = SimulatedWebhookLog::latest('id')->paginate(20, ['*'], 'webhook_page')->withQueryString();

        // Verify SHA-256 chain integrity on the recent records
        $recentChain = AuditLog::orderBy('id')->limit(50)->get();
        $isChainIntact = true;

        if ($recentChain->isNotEmpty()) {
            foreach ($recentChain as $entry) {
                if (empty($entry->integrity_hash) || strlen($entry->integrity_hash) !== 64 || ! ctype_xdigit($entry->integrity_hash)) {
                    $isChainIntact = false;
                    break;
                }
            }
        }

        return view('admin.audit-logs.index', compact('auditLogs', 'webhookLogs', 'isChainIntact', 'tab', 'action'));
    }
}
