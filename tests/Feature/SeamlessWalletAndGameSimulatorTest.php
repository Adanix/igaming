<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameRound;
use App\Models\PlayerBonus;
use App\Models\Promotion;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletLedgerEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeamlessWalletAndGameSimulatorTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $player;

    protected function setUp(): void
    {
        parent::setUp();
        $this->player = User::where('email', 'alex.mercer@demo.nexusplay.com')->firstOrFail();
    }

    public function test_player_lobby_displays_games_and_filters(): void
    {
        $response = $this->actingAs($this->player)->get('/lobby');

        $response->assertStatus(200);
        $response->assertSee('Gates of Olympus 1000');
        $response->assertSee('Sweet Bonanza Xmas');
        $response->assertSee('Aviator Sky Multiplier');
    }

    public function test_player_can_toggle_game_favorites(): void
    {
        $game = Game::where('slug', 'sweet-bonanza-xmas-sim')->firstOrFail();

        $response = $this->actingAs($this->player)->postJson('/lobby/favorite', [
            'game_id' => $game->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_favorite' => true]);

        // Toggle again to remove
        $response2 = $this->actingAs($this->player)->postJson('/lobby/favorite', [
            'game_id' => $game->id,
        ]);

        $response2->assertStatus(200);
        $response2->assertJson(['success' => true, 'is_favorite' => false]);
    }

    public function test_player_can_launch_game_stage(): void
    {
        $game = Game::where('slug', 'gates-of-olympus-sim')->firstOrFail();

        $response = $this->actingAs($this->player)->get("/play/{$game->game_code}");

        $response->assertStatus(200);
        $response->assertSee('Gates of Olympus 1000');
        $response->assertSee('Spin (RTP)');
        $response->assertSee('Aggregator Telemetry');
    }

    public function test_player_can_play_spin_and_balances_update_with_idempotent_ledger_row(): void
    {
        $game = Game::where('slug', 'gates-of-olympus-sim')->firstOrFail();
        $initialCash = (float) $this->player->wallet->cash_balance_usd;

        $response = $this->actingAs($this->player)->postJson("/play/{$game->game_code}/round", [
            'bet_amount' => 5.00,
            'outcome_mode' => 'loss',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $freshWallet = $this->player->wallet->fresh();
        $this->assertEquals($initialCash - 5.00, (float) $freshWallet->cash_balance_usd);

        // Verify ledger invariant
        $ledgerSum = (float) WalletLedgerEntry::where('wallet_id', $freshWallet->id)
            ->where('balance_bucket', 'cash')
            ->sum('amount_usd');

        $this->assertEquals((float) $freshWallet->cash_balance_usd, $ledgerSum);
    }

    public function test_player_deterministic_big_win_multiplies_payout(): void
    {
        $game = Game::where('slug', 'gates-of-olympus-sim')->firstOrFail();
        $initialCash = (float) $this->player->wallet->cash_balance_usd;

        $response = $this->actingAs($this->player)->postJson("/play/{$game->game_code}/round", [
            'bet_amount' => 10.00,
            'outcome_mode' => 'win_big', // 15x = $150 payout
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'payout_amount_usd' => 150.00,
                'outcome_type' => 'big_win',
            ],
        ]);

        $freshWallet = $this->player->wallet->fresh();
        $expectedCash = $initialCash - 10.00 + 150.00;
        $this->assertEquals($expectedCash, (float) $freshWallet->cash_balance_usd);
    }

    public function test_atomic_rollback_restores_debited_bet_to_player_wallet(): void
    {
        $game = Game::where('slug', 'gates-of-olympus-sim')->firstOrFail();
        $initialCash = (float) $this->player->wallet->cash_balance_usd;

        $response = $this->actingAs($this->player)->postJson("/play/{$game->game_code}/rollback", [
            'bet_amount' => 25.00,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $freshWallet = $this->player->wallet->fresh();
        // With rollback, net change is $0.00
        $this->assertEquals($initialCash, (float) $freshWallet->cash_balance_usd);

        // Verify ledger entries include bet debit and rollback credit
        $rollbackEntry = WalletLedgerEntry::where('wallet_id', $freshWallet->id)
            ->where('entry_type', 'game_rollback_credit')
            ->latest('id')
            ->first();

        $this->assertNotNull($rollbackEntry);
        $this->assertEquals(25.00, (float) $rollbackEntry->amount_usd);
    }

    public function test_bonus_rollover_increments_wagering_on_bet(): void
    {
        $game = Game::where('slug', 'gates-of-olympus-sim')->firstOrFail();
        $activeBonus = $this->player->activeBonus;
        $this->assertNotNull($activeBonus);

        $initialWagered = (float) $activeBonus->wagering_completed_usd;

        $this->actingAs($this->player)->postJson("/play/{$game->game_code}/round", [
            'bet_amount' => 5.00,
            'outcome_mode' => 'loss',
        ]);

        $freshBonus = $activeBonus->fresh();
        $this->assertEquals($initialWagered + 5.00, (float) $freshBonus->wagering_completed_usd);
    }

    public function test_player_vip_hub_renders_tiers_and_current_status(): void
    {
        $response = $this->actingAs($this->player)->get('/vip');

        $response->assertStatus(200);
        $response->assertSee('VIP Loyalty Club & Tier Status');
        $response->assertSee($this->player->vipTier->name);
        $response->assertSee('Instant Loss Recovery');
    }

    public function test_player_can_claim_vip_cashback(): void
    {
        $initialCash = (float) $this->player->wallet->cash_balance_usd;

        $response = $this->actingAs($this->player)->post('/vip/claim-cashback');

        $response->assertRedirect('/vip');
        $response->assertSessionHas('status');

        $freshCash = (float) $this->player->wallet->fresh()->cash_balance_usd;
        $this->assertGreaterThan($initialCash, $freshCash);
    }
}
