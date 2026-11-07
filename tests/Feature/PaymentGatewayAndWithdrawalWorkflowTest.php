<?php

namespace Tests\Feature;

use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\WalletLedgerEntry;
use App\Services\Simulators\SimulatedPaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewayAndWithdrawalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $player;
    protected User $opsStaff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->player = User::where('email', 'alex.mercer@demo.nexusplay.com')->firstOrFail();
        $this->opsStaff = User::where('email', 'nadia.kusuma@demo.nexusplay.com')->firstOrFail();
    }

    public function test_player_can_initiate_deposit_and_reach_sandbox(): void
    {
        $method = PaymentMethod::where('channel_type', 'crypto')->firstOrFail();

        $response = $this->actingAs($this->player)->post('/wallet/deposit', [
            'payment_method_id' => $method->id,
            'amount_usd' => 100.00,
        ]);

        $tx = PaymentTransaction::where('user_id', $this->player->id)
            ->where('type', 'deposit')
            ->latest('id')
            ->firstOrFail();

        $response->assertRedirect(route('wallet.deposit.sandbox', $tx->id));

        $sandboxResponse = $this->actingAs($this->player)->get(route('wallet.deposit.sandbox', $tx->id));
        $sandboxResponse->assertStatus(200);
        $sandboxResponse->assertSee($tx->reference);
        $sandboxResponse->assertSee('HMAC-SHA256 Signing Spec');
    }

    public function test_webhook_callback_verifies_valid_hmac_sha256_and_credits_cash_wallet(): void
    {
        $method = PaymentMethod::firstOrFail();
        $tx = PaymentTransaction::create([
            'user_id' => $this->player->id,
            'wallet_id' => $this->player->wallet->id,
            'payment_method_id' => $method->id,
            'type' => 'deposit',
            'amount_usd' => 200.00,
            'fee_usd' => 0.00,
            'net_amount_usd' => 200.00,
            'currency' => 'USD',
            'status' => 'pending',
            'reference' => 'TX-TEST-DEP-001',
            'idempotency_key' => 'idemp-test-001',
        ]);

        $initialCash = (float) $this->player->wallet->cash_balance_usd;

        $gateway = app(SimulatedPaymentGatewayService::class);
        $data = $gateway->buildPayload($tx, 'paid');

        $response = $this->postJson('/webhooks/payment/callback', [
            'payload' => $data['payload'],
            'signature' => $data['signature'],
            'outcome' => 'paid',
            'reference' => $tx->reference,
            'idempotency_key' => $data['idempotency_key'],
        ], ['X-Signature' => $data['signature']]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $freshWallet = $this->player->wallet->fresh();
        $this->assertEquals($initialCash + 200.00, (float) $freshWallet->cash_balance_usd);

        // Verify ledger balance invariant
        $ledgerSum = (float) WalletLedgerEntry::where('wallet_id', $freshWallet->id)
            ->where('balance_bucket', 'cash')
            ->sum('amount_usd');
        $this->assertEquals((float) $freshWallet->cash_balance_usd, $ledgerSum);
    }

    public function test_webhook_callback_rejects_invalid_hmac_signature(): void
    {
        $method = PaymentMethod::firstOrFail();
        $tx = PaymentTransaction::create([
            'user_id' => $this->player->id,
            'wallet_id' => $this->player->wallet->id,
            'payment_method_id' => $method->id,
            'type' => 'deposit',
            'amount_usd' => 50.00,
            'currency' => 'USD',
            'status' => 'pending',
            'reference' => 'TX-TEST-DEP-BADSIG',
            'idempotency_key' => 'idemp-test-badsig',
        ]);

        $response = $this->postJson('/webhooks/payment/callback', [
            'payload' => ['reference' => $tx->reference, 'outcome' => 'paid'],
            'signature' => 'invalid_fake_hmac_signature_1234567890',
            'outcome' => 'paid',
            'reference' => $tx->reference,
            'idempotency_key' => 'idemp-bad',
        ], ['X-Signature' => 'invalid_fake_hmac_signature_1234567890']);

        $response->assertStatus(401);
        $response->assertJson(['success' => false]);
    }

    public function test_webhook_callback_blocks_duplicate_replay_attack_with_zero_double_credit(): void
    {
        $method = PaymentMethod::firstOrFail();
        $tx = PaymentTransaction::create([
            'user_id' => $this->player->id,
            'wallet_id' => $this->player->wallet->id,
            'payment_method_id' => $method->id,
            'type' => 'deposit',
            'amount_usd' => 75.00,
            'currency' => 'USD',
            'status' => 'pending',
            'reference' => 'TX-TEST-DEP-REPLAY',
            'idempotency_key' => 'idemp-replay-001',
        ]);

        $gateway = app(SimulatedPaymentGatewayService::class);
        $data = $gateway->buildPayload($tx, 'paid');

        // First execution -> SUCCESS
        $res1 = $this->postJson('/webhooks/payment/callback', [
            'payload' => $data['payload'],
            'signature' => $data['signature'],
            'outcome' => 'paid',
            'reference' => $tx->reference,
            'idempotency_key' => $data['idempotency_key'],
        ], ['X-Signature' => $data['signature']]);
        $res1->assertStatus(200);

        $cashAfterFirst = (float) $this->player->wallet->fresh()->cash_balance_usd;

        // Second duplicate replay execution -> BLOCKED (Duplicate detected)
        $res2 = $this->postJson('/webhooks/payment/callback', [
            'payload' => $data['payload'],
            'signature' => $data['signature'],
            'outcome' => 'paid',
            'reference' => $tx->reference,
            'idempotency_key' => $data['idempotency_key'],
        ], ['X-Signature' => $data['signature']]);

        $res2->assertStatus(200);
        $res2->assertJson(['duplicate' => true]);

        // Exact zero double credit verification
        $cashAfterSecond = (float) $this->player->wallet->fresh()->cash_balance_usd;
        $this->assertEquals($cashAfterFirst, $cashAfterSecond);
    }

    public function test_player_can_request_standard_withdrawal_and_locks_cash(): void
    {
        $method = PaymentMethod::firstOrFail();
        $initialCash = (float) $this->player->wallet->cash_balance_usd;
        $initialLocked = (float) $this->player->wallet->locked_withdrawal_usd;

        $response = $this->actingAs($this->player)->post('/wallet/withdraw', [
            'payment_method_id' => $method->id,
            'amount_usd' => 50.00,
            'account_details' => 'TRC20: TLsV52s89zN83kLm',
        ]);

        $response->assertRedirect(route('wallet.index'));

        $freshWallet = $this->player->wallet->fresh();
        $this->assertEquals($initialCash - 50.00, (float) $freshWallet->cash_balance_usd);
        $this->assertEquals($initialLocked + 50.00, (float) $freshWallet->locked_withdrawal_usd);
    }

    public function test_player_can_cancel_pending_withdrawal_and_restores_cash(): void
    {
        $method = PaymentMethod::firstOrFail();
        $this->actingAs($this->player)->post('/wallet/withdraw', [
            'payment_method_id' => $method->id,
            'amount_usd' => 30.00,
            'account_details' => 'TRC20: TLsV52s89zN83kLm',
        ]);

        $tx = PaymentTransaction::where('user_id', $this->player->id)
            ->where('type', 'withdrawal')
            ->whereIn('status', ['pending', 'pending_review'])
            ->latest('id')
            ->firstOrFail();

        $cashBeforeCancel = (float) $this->player->wallet->fresh()->cash_balance_usd;

        $response = $this->actingAs($this->player)->post("/wallet/withdraw/{$tx->id}/cancel");
        $response->assertRedirect(route('wallet.index'));

        $freshWallet = $this->player->wallet->fresh();
        $this->assertEquals($cashBeforeCancel + 30.00, (float) $freshWallet->cash_balance_usd);
        $this->assertEquals('cancelled', $tx->fresh()->status);
    }

    public function test_high_value_withdrawal_sets_flag_and_ops_can_approve(): void
    {
        $vipPlayer = User::where('email', 'victoria.sterling@demo.nexusplay.com')->firstOrFail();

        // Victoria has existing pending $1,500 withdrawal from seeder
        $tx = PaymentTransaction::where('user_id', $vipPlayer->id)
            ->where('type', 'withdrawal')
            ->whereIn('status', ['pending', 'pending_review'])
            ->where('is_high_value', true)
            ->firstOrFail();

        $this->assertTrue((bool) $tx->is_high_value);

        // Ops Staff Nadia approves it
        $this->actingAs($this->opsStaff);
        session(['admin_mfa_verified_at' => now()->toIso8601String()]);

        $response = $this->post("/admin/payments/{$tx->id}/approve", [
            'note' => 'Approved VIP withdrawal after KYC verification',
        ]);

        $response->assertRedirect();
        $this->assertEquals('paid', $tx->fresh()->status);
    }
}
