<?php

namespace App\Contracts;

use App\Models\PaymentTransaction;

interface PaymentGatewayInterface
{
    public function buildPayload(PaymentTransaction $transaction, string $outcome = 'paid', ?string $customIdemp = null): array;

    public function processCallback(
        string $reference,
        string $outcome,
        string $idempotencyKey,
        string $signature,
        array $rawPayload
    ): array;
}
