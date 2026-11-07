<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Simulators\SimulatedPaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected SimulatedPaymentGatewayService $gatewayService
    ) {}

    /**
     * Handle incoming payment gateway webhook callback.
     * Validates HMAC-SHA256 signature and updates transaction/ledger.
     */
    public function handleCallback(Request $request): JsonResponse
    {
        $signature = $request->header('X-Signature', $request->input('signature', ''));
        $rawPayload = $request->input('payload', $request->all());

        // Normalize if payload is nested
        if (is_string($rawPayload)) {
            $rawPayload = json_decode($rawPayload, true) ?? [];
        }

        $reference = $rawPayload['reference'] ?? $request->input('reference', '');
        $outcome = $rawPayload['outcome'] ?? $request->input('outcome', 'paid');
        $idempotencyKey = $rawPayload['idempotency_key'] ?? $request->input('idempotency_key', '');

        $result = $this->gatewayService->processCallback(
            $reference,
            $outcome,
            $idempotencyKey,
            $signature,
            $rawPayload
        );

        $statusCode = $result['code'] ?? ($result['success'] ? 200 : 400);

        return response()->json($result, $statusCode);
    }
}
