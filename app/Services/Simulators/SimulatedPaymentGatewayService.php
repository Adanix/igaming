<?php

namespace App\Services\Simulators;

use App\Models\PaymentTransaction;
use App\Models\SimulatedWebhookLog;
use App\Services\WalletLedgerService;
use Illuminate\Support\Str;

class SimulatedPaymentGatewayService
{
    public function __construct(
        protected WalletLedgerService $ledgerService
    ) {}

    /**
     * Build signed simulated payment payload preview for a deposit invoice.
     */
    public function buildPayload(PaymentTransaction $transaction, string $outcome = 'paid', ?string $customIdemp = null): array
    {
        $idempKey = $customIdemp ?? ('idemp-pay-' . $transaction->reference . '-' . Str::random(6));

        $payload = [
            'event' => 'payment.callback',
            'reference' => $transaction->reference,
            'merchant_order_id' => 'ORD-' . $transaction->id,
            'amount_usd' => (float) $transaction->amount_usd,
            'currency' => 'USD',
            'method' => $transaction->paymentMethod->code,
            'outcome' => $outcome,
            'idempotency_key' => $idempKey,
            'timestamp' => now()->toIso8601String(),
        ];

        $payloadJson = json_encode($payload);
        $secret = config('demo.webhook_secret');
        $signature = hash_hmac('sha256', $payloadJson, $secret);

        return [
            'payload' => $payload,
            'payload_json' => $payloadJson,
            'signature' => $signature,
            'idempotency_key' => $idempKey,
        ];
    }

    /**
     * Process an incoming payment webhook callback.
     */
    public function processCallback(
        string $reference,
        string $outcome,
        string $idempotencyKey,
        string $signature,
        array $rawPayload
    ): array {
        $secret = config('demo.webhook_secret');
        $expectedSignature = hash_hmac('sha256', json_encode($rawPayload), $secret);
        $sigValid = hash_equals($expectedSignature, $signature);

        if (! $sigValid) {
            SimulatedWebhookLog::create([
                'source_type' => 'payment_gateway',
                'event_name' => 'payment.callback',
                'reference' => $reference,
                'idempotency_key' => $idempotencyKey,
                'signature_header' => $signature,
                'signature_valid' => false,
                'processing_outcome' => 'rejected_invalid_signature',
                'payload_json' => $rawPayload,
                'response_json' => ['error' => 'Invalid HMAC Signature'],
                'created_at' => now(),
            ]);

            return [
                'success' => false,
                'code' => 401,
                'message' => 'Invalid HMAC signature.',
            ];
        }

        $transaction = PaymentTransaction::where('reference', $reference)->first();
        if (! $transaction) {
            return [
                'success' => false,
                'code' => 404,
                'message' => "Transaction '{$reference}' not found.",
            ];
        }

        // Idempotency check: if transaction is already paid, or outcome is duplicate_replay
        if ($transaction->status === 'paid' || $outcome === 'duplicate_replay') {
            SimulatedWebhookLog::create([
                'source_type' => 'payment_gateway',
                'event_name' => 'payment.callback',
                'reference' => $reference,
                'idempotency_key' => $idempotencyKey,
                'signature_header' => $signature,
                'signature_valid' => true,
                'processing_outcome' => 'duplicate_replay_blocked',
                'payload_json' => $rawPayload,
                'response_json' => [
                    'status' => 'ALREADY_PROCESSED',
                    'reference' => $reference,
                    'message' => 'Duplicate callback detected and safely blocked. Zero double-credit.',
                ],
                'created_at' => now(),
            ]);

            return [
                'success' => true,
                'code' => 200,
                'duplicate' => true,
                'message' => 'Duplicate webhook replay blocked successfully. Wallet balance unchanged.',
                'transaction' => $transaction,
            ];
        }

        // Handle outcomes
        if ($outcome === 'paid') {
            $transaction->status = 'paid';
            $transaction->completed_at = now();
            $transaction->save();

            $this->ledgerService->creditDeposit($transaction);

            $resultOutcome = 'processed';
            $msg = 'Payment successfully verified and USD credited to wallet.';
        } elseif ($outcome === 'failed') {
            $transaction->status = 'failed';
            $transaction->completed_at = now();
            $transaction->save();

            $resultOutcome = 'processed';
            $msg = 'Simulated payment decline recorded. Wallet unchanged.';
        } else {
            // expired
            $transaction->status = 'expired';
            $transaction->completed_at = now();
            $transaction->save();

            $resultOutcome = 'processed';
            $msg = 'Simulated payment expiration recorded. Wallet unchanged.';
        }

        SimulatedWebhookLog::create([
            'source_type' => 'payment_gateway',
            'event_name' => 'payment.callback',
            'reference' => $reference,
            'idempotency_key' => $idempotencyKey,
            'signature_header' => $signature,
            'signature_valid' => true,
            'processing_outcome' => $resultOutcome,
            'payload_json' => $rawPayload,
            'response_json' => [
                'acknowledged' => true,
                'reference' => $reference,
                'new_status' => $transaction->status,
            ],
            'created_at' => now(),
        ]);

        return [
            'success' => true,
            'code' => 200,
            'duplicate' => false,
            'message' => $msg,
            'transaction' => $transaction->fresh(),
        ];
    }
}
