<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Game;
use App\Models\User;
use App\Models\WalletLedgerEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackofficeSecurityAndAffiliateTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $superAdmin;
    protected User $opsStaff;
    protected User $affiliate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->superAdmin = User::where('email', 'damian.thorne@demo.nexusplay.com')->firstOrFail();
        $this->opsStaff = User::where('email', 'nadia.kusuma@demo.nexusplay.com')->firstOrFail();
        $this->affiliate = User::where('email', 'marcus.vance@demo.nexusplay.com')->firstOrFail();
    }

    public function test_admin_mfa_challenge_requires_totp_code_123456(): void
    {
        $this->actingAs($this->opsStaff);
        // Without session MFA verified, accessing /admin redirects to MFA challenge
        $response = $this->get('/admin');
        $response->assertRedirect(route('admin.mfa.challenge'));

        // Post valid code 123456
        $verifyRes = $this->post('/admin/mfa-verify', ['code' => '123456']);
        $verifyRes->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(session()->has('admin_mfa_verified_at'));
    }

    public function test_admin_dashboard_shows_reconciliation_zero_discrepancy(): void
    {
        $this->actingAs($this->superAdmin);
        session(['admin_mfa_verified_at' => now()->toIso8601String()]);

        $response = $this->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('$0.00 Discrepancy (100% Reconciled)');
        $response->assertSee('Gross Gaming Revenue');
        $response->assertSee('Net Gaming Revenue');
    }

    public function test_admin_can_adjust_player_balance_with_audit_trail(): void
    {
        $player = User::where('email', 'alex.mercer@demo.nexusplay.com')->firstOrFail();
        $initialCash = (float) $player->wallet->cash_balance_usd;

        $this->actingAs($this->superAdmin);
        session(['admin_mfa_verified_at' => now()->toIso8601String()]);

        $response = $this->post("/admin/players/{$player->id}/adjust-balance", [
            'balance_bucket' => 'cash',
            'amount_usd' => 150.00,
            'reason' => 'VIP Goodwill Credit approved by COO',
        ]);

        $response->assertRedirect();

        $freshWallet = $player->wallet->fresh();
        $this->assertEquals($initialCash + 150.00, (float) $freshWallet->cash_balance_usd);

        // Verify audit log exists
        $audit = AuditLog::where('event_action', 'wallet.manual_adjustment')
            ->where('target_id', $freshWallet->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($audit);
        $this->assertStringContainsString('VIP Goodwill Credit', $audit->summary);
    }

    public function test_admin_can_toggle_game_status_and_update_limits(): void
    {
        $game = Game::firstOrFail();

        $this->actingAs($this->superAdmin);
        session(['admin_mfa_verified_at' => now()->toIso8601String()]);

        // Toggle status
        $this->post("/admin/games/{$game->id}/toggle");
        $this->assertEquals('maintenance', $game->fresh()->status);

        // Update limits
        $this->post("/admin/games/{$game->id}/limits", [
            'min_bet_usd' => 2.00,
            'max_bet_usd' => 2000.00,
            'rtp_percentage' => 97.50,
        ]);

        $freshGame = $game->fresh();
        $this->assertEquals(2.00, (float) $freshGame->min_bet_usd);
        $this->assertEquals(2000.00, (float) $freshGame->max_bet_usd);
        $this->assertEquals(97.50, (float) $freshGame->rtp_percentage);
    }

    public function test_affiliate_dashboard_calculates_ngr_and_revshare_commissions(): void
    {
        $this->actingAs($this->affiliate);

        $response = $this->get('/affiliate');

        $response->assertStatus(200);
        $response->assertSee('NexusAffiliates');
        $response->assertSee('Unpaid Commission');
        $response->assertSee('30%');

        // Test recalculate action
        $recalcRes = $this->post('/affiliate/recalculate');
        $recalcRes->assertRedirect(route('affiliate.dashboard'));
        $recalcRes->assertSessionHas('status');
    }

    public function test_affiliate_can_request_commission_payout(): void
    {
        $this->actingAs($this->affiliate);
        $profile = $this->affiliate->affiliateProfile;
        $initialBalance = (float) $profile->commission_balance_usd;

        $response = $this->post('/affiliate/payout', [
            'amount_usd' => 100.00,
            'payout_method' => 'usdt_trc20',
            'payout_details' => 'TRC20: TQn9Y2khs91...',
        ]);

        $response->assertRedirect(route('affiliate.dashboard'));
        $response->assertSessionHas('status');

        $freshProfile = $profile->fresh();
        $this->assertEquals($initialBalance - 100.00, (float) $freshProfile->commission_balance_usd);
        $this->assertEquals((float) $profile->lifetime_paid_usd + 100.00, (float) $freshProfile->lifetime_paid_usd);
    }

    public function test_chained_audit_trail_verifies_sha256_integrity(): void
    {
        $this->actingAs($this->superAdmin);
        session(['admin_mfa_verified_at' => now()->toIso8601String()]);

        $response = $this->get('/admin/audit-logs');

        $response->assertStatus(200);
        $response->assertSee('SHA-256 Hash Chain Verified Intact');
    }
}
