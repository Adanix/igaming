<?php

namespace App\Services\Simulators;

use App\Models\User;
use App\Services\AuditLoggerService;

class SimulatedMfaService
{
    public const DEMO_TOTP_CODE = '123456';

    public function __construct(
        protected AuditLoggerService $auditLogger
    ) {}

    public function verifyCode(User $user, string $code): bool
    {
        $code = trim($code);

        if ($code === self::DEMO_TOTP_CODE) {
            session(['admin_mfa_verified_at' => now()->toIso8601String()]);

            $this->auditLogger->log(
                'admin.mfa_verified',
                $user,
                User::class,
                $user->id,
                'Simulated TOTP MFA challenge completed successfully'
            );

            return true;
        }

        $this->auditLogger->log(
            'admin.mfa_failed',
            $user,
            User::class,
            $user->id,
            'Invalid TOTP code attempted: ' . substr($code, 0, 3) . '***'
        );

        return false;
    }
}
