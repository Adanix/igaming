<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditLoggerService
{
    /**
     * Log a privileged or sensitive domain event with chained SHA-256 integrity hash.
     */
    public function log(
        string $eventAction,
        ?User $actor = null,
        ?string $targetType = null,
        ?int $targetId = null,
        ?string $reason = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        $actor = $actor ?? auth()->user();
        $actorId = $actor ? $actor->id : null;
        $actorRole = $actor ? $actor->role : 'system';
        $ipAddress = request()->ip() ?? '127.0.0.1';

        $lastLog = AuditLog::latest('id')->first();
        $prevHash = $lastLog ? $lastLog->integrity_hash : '0000000000000000000000000000000000000000000000000000000000000000';

        $rawForHash = implode('|', [
            $prevHash,
            $actorId,
            $actorRole,
            $eventAction,
            $targetType,
            $targetId,
            $reason,
            json_encode($oldValues),
            json_encode($newValues),
            $ipAddress,
            microtime(true),
        ]);

        $integrityHash = hash('sha256', $rawForHash);

        return AuditLog::create([
            'actor_user_id' => $actorId,
            'actor_role' => $actorRole,
            'event_action' => $eventAction,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'reason' => $reason,
            'old_values_json' => $oldValues,
            'new_values_json' => $newValues,
            'ip_address' => $ipAddress,
            'integrity_hash' => $integrityHash,
            'created_at' => now(),
        ]);
    }
}
