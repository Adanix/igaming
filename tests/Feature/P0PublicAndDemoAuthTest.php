<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class P0PublicAndDemoAuthTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_public_home_renders_with_featured_games_and_bilingual_toggle(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('NexusPlay');
        $response->assertSee('Turnkey iGaming Platform');
        $response->assertSee('nixresponding@gmail.com');
    }

    public function test_public_docs_renders_all_6_sections_and_customization_callout(): void
    {
        $response = $this->get('/docs');

        $response->assertStatus(200);
        $response->assertSee('Architecture &amp; Seamless Wallet Protocol', false);
        $response->assertSee('Simulated Game Aggregator &amp; Telemetry', false);
        $response->assertSee('Payment Gateway &amp; Webhook Simulator', false);
        $response->assertSee('Role-Based Access Control &amp; Admin MFA', false);
        $response->assertSee('Affiliate Multi-Tier Revenue Share', false);
        $response->assertSee('Demo Personas &amp; Testing Guide', false);
        $response->assertSee('Looking for Custom Production Integrations?');
        $response->assertSee('nixresponding@gmail.com');
    }

    public function test_locale_switcher_toggles_between_en_and_id(): void
    {
        $response = $this->post('/locale/switch', ['locale' => 'id']);
        $response->assertRedirect();
        $response->assertSessionHas('locale', 'id');

        $responseEn = $this->post('/locale/switch', ['locale' => 'en']);
        $responseEn->assertRedirect();
        $responseEn->assertSessionHas('locale', 'en');
    }

    public function test_demo_login_displays_all_5_personas_with_live_balances_and_no_password_inputs(): void
    {
        $response = $this->get('/demo/login');

        $response->assertStatus(200);
        $response->assertSee('Alex Mercer');
        $response->assertSee('Victoria Sterling');
        $response->assertSee('Marcus Vance');
        $response->assertSee('Nadia Kusuma');
        $response->assertSee('Damian Thorne');
        $response->assertDontSee('type="password"', false);
    }

    public function test_quick_login_authenticates_standard_player_and_redirects_to_lobby(): void
    {
        $response = $this->post('/demo/quick-login', [
            'persona_key' => 'player_standard',
        ]);

        $response->assertRedirect(route('lobby.index'));
        $this->assertAuthenticated();
        $this->assertEquals('alex.mercer@demo.nexusplay.com', auth()->user()->email);
    }

    public function test_quick_login_authenticates_super_admin_with_mfa_bypass_redirects_to_admin(): void
    {
        $response = $this->post('/demo/quick-login', [
            'persona_key' => 'super_admin',
            'skip_mfa' => 1,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('damian.thorne@demo.nexusplay.com', auth()->user()->email);
        $this->assertTrue(session()->has('admin_mfa_verified_at'));
    }

    public function test_logout_invalidates_session_and_redirects_to_demo_login(): void
    {
        $user = User::where('email', 'alex.mercer@demo.nexusplay.com')->firstOrFail();
        $this->actingAs($user);

        $response = $this->post('/logout');

        $response->assertRedirect(route('demo.login'));
        $this->assertGuest();
    }

    public function test_demo_reset_restores_database_and_redirects_with_status(): void
    {
        $response = $this->post('/demo/reset');

        $response->assertRedirect(route('demo.login'));
        $response->assertSessionHas('status');
    }
}
