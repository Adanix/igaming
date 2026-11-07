<?php

namespace Database\Seeders;

use App\Models\AffiliateCommission;
use App\Models\AffiliateProfile;
use App\Models\AffiliateReferral;
use App\Models\AuditLog;
use App\Models\Game;
use App\Models\GameFavorite;
use App\Models\GameProvider;
use App\Models\GameRound;
use App\Models\GameSession;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\PlayerBonus;
use App\Models\Promotion;
use App\Models\SimulatedWebhookLog;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\VipTier;
use App\Models\Wallet;
use App\Models\WalletLedgerEntry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. VIP Tiers
        $tiers = [
            'bronze' => VipTier::create([
                'slug' => 'bronze',
                'name_en' => 'Bronze VIP',
                'name_id' => 'VIP Bronze',
                'min_wagered_usd' => 0.00,
                'cashback_rate_pct' => 2.00,
                'daily_withdrawal_limit_usd' => 5000.00,
                'badge_color' => 'amber-700',
            ]),
            'silver' => VipTier::create([
                'slug' => 'silver',
                'name_en' => 'Silver VIP',
                'name_id' => 'VIP Silver',
                'min_wagered_usd' => 2500.00,
                'cashback_rate_pct' => 5.00,
                'daily_withdrawal_limit_usd' => 10000.00,
                'badge_color' => 'slate-300',
            ]),
            'gold' => VipTier::create([
                'slug' => 'gold',
                'name_en' => 'Gold VIP',
                'name_id' => 'VIP Gold',
                'min_wagered_usd' => 10000.00,
                'cashback_rate_pct' => 8.00,
                'daily_withdrawal_limit_usd' => 25000.00,
                'badge_color' => 'amber-400',
            ]),
            'platinum' => VipTier::create([
                'slug' => 'platinum',
                'name_en' => 'Platinum VIP',
                'name_id' => 'VIP Platinum',
                'min_wagered_usd' => 50000.00,
                'cashback_rate_pct' => 12.00,
                'daily_withdrawal_limit_usd' => 100000.00,
                'badge_color' => 'cyan-400',
            ]),
        ];

        // 2. System Settings
        $settings = [
            [
                'setting_key' => 'global_min_deposit_usd',
                'setting_value' => '10.00',
                'value_type' => 'decimal',
                'description_en' => 'Minimum allowable deposit in USD across all payment rails.',
                'description_id' => 'Batas minimum deposit dalam USD di semua metode pembayaran.',
            ],
            [
                'setting_key' => 'global_max_deposit_usd',
                'setting_value' => '50000.00',
                'value_type' => 'decimal',
                'description_en' => 'Maximum allowable single deposit in USD.',
                'description_id' => 'Batas maksimum deposit tunggal dalam USD.',
            ],
            [
                'setting_key' => 'high_value_withdrawal_threshold_usd',
                'setting_value' => '1000.00',
                'value_type' => 'decimal',
                'description_en' => 'Dual-control threshold requiring Super Admin approval.',
                'description_id' => 'Batas persetujuan ganda yang mewajibkan izin Super Admin.',
            ],
            [
                'setting_key' => 'default_wagering_multiplier',
                'setting_value' => '15.00',
                'value_type' => 'decimal',
                'description_en' => 'Default wagering requirement rollover multiplier.',
                'description_id' => 'Pengali rollover default untuk persyaratan turnover bonus.',
            ],
            [
                'setting_key' => 'maintenance_mode',
                'setting_value' => 'false',
                'value_type' => 'boolean',
                'description_en' => 'Global maintenance toggle for public storefront.',
                'description_id' => 'Status pemeliharaan global untuk tampilan publik.',
            ],
            [
                'setting_key' => 'contact_email',
                'setting_value' => 'nixresponding@gmail.com',
                'value_type' => 'string',
                'description_en' => 'Primary platform operator and commercial customization contact.',
                'description_id' => 'Kontak utama operator platform dan kustomisasi komersial.',
            ],
        ];
        foreach ($settings as $s) {
            SystemSetting::create($s);
        }

        // 3. Game Providers
        $providers = [
            'pragmatic' => GameProvider::create([
                'code' => 'pragmatic_sim',
                'name' => 'Pragmatic Play Sim',
                'category_focus' => 'Slots & Live Games',
                'status' => 'active',
                'aggregator_endpoint_sim' => 'https://aggregator.sim.local/pragmatic',
            ]),
            'evolution' => GameProvider::create([
                'code' => 'evolution_sim',
                'name' => 'Evolution Gaming Sim',
                'category_focus' => 'Live Dealer Casino',
                'status' => 'active',
                'aggregator_endpoint_sim' => 'https://aggregator.sim.local/evolution',
            ]),
            'pgsoft' => GameProvider::create([
                'code' => 'pgsoft_sim',
                'name' => 'PG Soft Sim',
                'category_focus' => 'Mobile Video Slots',
                'status' => 'active',
                'aggregator_endpoint_sim' => 'https://aggregator.sim.local/pgsoft',
            ]),
            'hacksaw' => GameProvider::create([
                'code' => 'hacksaw_sim',
                'name' => 'Hacksaw Gaming Sim',
                'category_focus' => 'High Volatility Slots',
                'status' => 'active',
                'aggregator_endpoint_sim' => 'https://aggregator.sim.local/hacksaw',
            ]),
            'spribe' => GameProvider::create([
                'code' => 'spribe_sim',
                'name' => 'Spribe Sim',
                'category_focus' => 'Instant & Crash Games',
                'status' => 'active',
                'aggregator_endpoint_sim' => 'https://aggregator.sim.local/spribe',
            ]),
        ];

        // 4. 16 Diverse Casino Games
        $gamesData = [
            // Slots
            [
                'provider' => 'pragmatic',
                'slug' => 'gates-of-olympus-sim',
                'game_code' => 'vs20olympx',
                'title' => 'Gates of Olympus 1000',
                'category' => 'slots',
                'rtp' => 96.50,
                'volatility' => 'high',
                'min_bet' => 0.20,
                'max_bet' => 500.00,
                'contrib' => 100.00,
                'featured' => true,
                'gradient' => 'from-amber-600 to-indigo-900',
            ],
            [
                'provider' => 'pragmatic',
                'slug' => 'sweet-bonanza-xmas-sim',
                'game_code' => 'vs20swbonx',
                'title' => 'Sweet Bonanza Xmas',
                'category' => 'slots',
                'rtp' => 96.48,
                'volatility' => 'medium',
                'min_bet' => 0.20,
                'max_bet' => 400.00,
                'contrib' => 100.00,
                'featured' => true,
                'gradient' => 'from-rose-500 to-pink-900',
            ],
            [
                'provider' => 'hacksaw',
                'slug' => 'cyber-heist-megaways',
                'game_code' => 'hs_cyber_mw',
                'title' => 'Cyber Heist Megaways',
                'category' => 'slots',
                'rtp' => 96.65,
                'volatility' => 'high',
                'min_bet' => 0.40,
                'max_bet' => 600.00,
                'contrib' => 100.00,
                'featured' => true,
                'gradient' => 'from-emerald-500 to-cyan-900',
            ],
            [
                'provider' => 'pragmatic',
                'slug' => 'sugar-rush-fever',
                'game_code' => 'vs20sugarrush',
                'title' => 'Sugar Rush Fever',
                'category' => 'slots',
                'rtp' => 96.52,
                'volatility' => 'high',
                'min_bet' => 0.20,
                'max_bet' => 500.00,
                'contrib' => 100.00,
                'featured' => false,
                'gradient' => 'from-purple-600 to-rose-900',
            ],
            [
                'provider' => 'pragmatic',
                'slug' => 'starlight-princess-1000',
                'game_code' => 'vs20starlight',
                'title' => 'Starlight Princess 1000',
                'category' => 'slots',
                'rtp' => 96.50,
                'volatility' => 'high',
                'min_bet' => 0.20,
                'max_bet' => 500.00,
                'contrib' => 100.00,
                'featured' => true,
                'gradient' => 'from-sky-500 to-violet-900',
            ],
            [
                'provider' => 'hacksaw',
                'slug' => 'wanted-dead-or-wild-sim',
                'game_code' => 'hs_wanted_sim',
                'title' => 'Wanted Dead or a Wild',
                'category' => 'slots',
                'rtp' => 96.38,
                'volatility' => 'high',
                'min_bet' => 0.20,
                'max_bet' => 500.00,
                'contrib' => 100.00,
                'featured' => true,
                'gradient' => 'from-stone-700 to-amber-950',
            ],
            // Live Casino
            [
                'provider' => 'evolution',
                'slug' => 'lightning-roulette-vip',
                'game_code' => 'evo_lr_vip',
                'title' => 'Lightning Roulette VIP',
                'category' => 'live_casino',
                'rtp' => 97.30,
                'volatility' => 'high',
                'min_bet' => 1.00,
                'max_bet' => 5000.00,
                'contrib' => 20.00,
                'featured' => true,
                'gradient' => 'from-amber-500 to-yellow-950',
            ],
            [
                'provider' => 'evolution',
                'slug' => 'speed-baccarat-pro',
                'game_code' => 'evo_bac_spd',
                'title' => 'Speed Baccarat Pro',
                'category' => 'live_casino',
                'rtp' => 98.94,
                'volatility' => 'low',
                'min_bet' => 2.00,
                'max_bet' => 10000.00,
                'contrib' => 20.00,
                'featured' => true,
                'gradient' => 'from-red-600 to-slate-900',
            ],
            [
                'provider' => 'evolution',
                'slug' => 'blackjack-royale-usd',
                'game_code' => 'evo_bj_usd',
                'title' => 'Blackjack Royale USD',
                'category' => 'live_casino',
                'rtp' => 99.28,
                'volatility' => 'low',
                'min_bet' => 5.00,
                'max_bet' => 10000.00,
                'contrib' => 20.00,
                'featured' => true,
                'gradient' => 'from-blue-700 to-slate-950',
            ],
            [
                'provider' => 'evolution',
                'slug' => 'crazy-time-live-sim',
                'game_code' => 'evo_crazy_time',
                'title' => 'Crazy Time Live Sim',
                'category' => 'live_casino',
                'rtp' => 96.08,
                'volatility' => 'high',
                'min_bet' => 0.50,
                'max_bet' => 2500.00,
                'contrib' => 20.00,
                'featured' => false,
                'gradient' => 'from-fuchsia-600 to-purple-950',
            ],
            // Table Games
            [
                'provider' => 'pgsoft',
                'slug' => 'european-roulette-pro',
                'game_code' => 'pg_eur_roulette',
                'title' => 'European Roulette Pro',
                'category' => 'table_games',
                'rtp' => 97.30,
                'volatility' => 'medium',
                'min_bet' => 1.00,
                'max_bet' => 2000.00,
                'contrib' => 20.00,
                'featured' => false,
                'gradient' => 'from-emerald-700 to-teal-950',
            ],
            [
                'provider' => 'pgsoft',
                'slug' => 'texas-holdem-bonus',
                'game_code' => 'pg_tex_bonus',
                'title' => 'Texas Hold\'em Bonus',
                'category' => 'table_games',
                'rtp' => 97.96,
                'volatility' => 'medium',
                'min_bet' => 2.00,
                'max_bet' => 1000.00,
                'contrib' => 20.00,
                'featured' => false,
                'gradient' => 'from-amber-700 to-stone-900',
            ],
            [
                'provider' => 'pgsoft',
                'slug' => 'classic-baccarat-vip',
                'game_code' => 'pg_bac_vip',
                'title' => 'Classic Baccarat VIP',
                'category' => 'table_games',
                'rtp' => 98.76,
                'volatility' => 'low',
                'min_bet' => 5.00,
                'max_bet' => 5000.00,
                'contrib' => 20.00,
                'featured' => false,
                'gradient' => 'from-indigo-700 to-slate-900',
            ],
            // Crash & Instant
            [
                'provider' => 'spribe',
                'slug' => 'aviator-sky-multiplier',
                'game_code' => 'sp_aviator_usd',
                'title' => 'Aviator Sky Multiplier',
                'category' => 'crash_instant',
                'rtp' => 97.00,
                'volatility' => 'medium',
                'min_bet' => 0.10,
                'max_bet' => 200.00,
                'contrib' => 100.00,
                'featured' => true,
                'gradient' => 'from-red-600 to-zinc-950',
            ],
            [
                'provider' => 'spribe',
                'slug' => 'mines-gold-usd',
                'game_code' => 'sp_mines_gold',
                'title' => 'Mines Gold USD',
                'category' => 'crash_instant',
                'rtp' => 97.00,
                'volatility' => 'low',
                'min_bet' => 0.20,
                'max_bet' => 500.00,
                'contrib' => 100.00,
                'featured' => true,
                'gradient' => 'from-yellow-600 to-amber-950',
            ],
            [
                'provider' => 'spribe',
                'slug' => 'plinko-1000x-sim',
                'game_code' => 'sp_plinko_1000',
                'title' => 'Plinko 1000x Sim',
                'category' => 'crash_instant',
                'rtp' => 99.00,
                'volatility' => 'medium',
                'min_bet' => 0.10,
                'max_bet' => 1000.00,
                'contrib' => 100.00,
                'featured' => false,
                'gradient' => 'from-teal-600 to-slate-900',
            ],
        ];

        $games = [];
        foreach ($gamesData as $g) {
            $games[$g['slug']] = Game::create([
                'game_provider_id' => $providers[$g['provider']]->id,
                'slug' => $g['slug'],
                'game_code' => $g['game_code'],
                'title' => $g['title'],
                'category' => $g['category'],
                'rtp_percentage' => $g['rtp'],
                'volatility' => $g['volatility'],
                'min_bet_usd' => $g['min_bet'],
                'max_bet_usd' => $g['max_bet'],
                'wagering_contribution_pct' => $g['contrib'],
                'is_featured' => $g['featured'],
                'status' => 'active',
                'thumbnail_gradient' => $g['gradient'],
            ]);
        }

        // 5. Payment Methods
        $paymentMethods = [
            'usdt' => PaymentMethod::create([
                'code' => 'usdt_trc20',
                'name_en' => 'USDT (TRC-20 Crypto)',
                'name_id' => 'Kripto USDT (TRC-20)',
                'channel_type' => 'crypto',
                'supports_deposit' => true,
                'supports_withdrawal' => true,
                'min_deposit_usd' => 10.00,
                'max_deposit_usd' => 50000.00,
                'min_withdrawal_usd' => 20.00,
                'max_withdrawal_usd' => 25000.00,
                'status' => 'active',
            ]),
            'card' => PaymentMethod::create([
                'code' => 'stripe_card',
                'name_en' => 'Visa / Mastercard (USD)',
                'name_id' => 'Kartu Visa / Mastercard (USD)',
                'channel_type' => 'card',
                'supports_deposit' => true,
                'supports_withdrawal' => false,
                'min_deposit_usd' => 10.00,
                'max_deposit_usd' => 5000.00,
                'min_withdrawal_usd' => 0.00,
                'max_withdrawal_usd' => 0.00,
                'status' => 'active',
            ]),
            'wire' => PaymentMethod::create([
                'code' => 'bank_wire_usd',
                'name_en' => 'USD Instant Wire / Virtual Account',
                'name_id' => 'Transfer Bank USD Virtual',
                'channel_type' => 'bank_wire',
                'supports_deposit' => true,
                'supports_withdrawal' => true,
                'min_deposit_usd' => 25.00,
                'max_deposit_usd' => 25000.00,
                'min_withdrawal_usd' => 50.00,
                'max_withdrawal_usd' => 50000.00,
                'status' => 'active',
            ]),
            'ewallet' => PaymentMethod::create([
                'code' => 'ewallet_global',
                'name_en' => 'PayPal / Skrill E-Wallet (USD)',
                'name_id' => 'Dompet Digital PayPal / Skrill (USD)',
                'channel_type' => 'ewallet',
                'supports_deposit' => true,
                'supports_withdrawal' => true,
                'min_deposit_usd' => 10.00,
                'max_deposit_usd' => 10000.00,
                'min_withdrawal_usd' => 20.00,
                'max_withdrawal_usd' => 10000.00,
                'status' => 'active',
            ]),
        ];

        // 6. Promotions
        $promotions = [
            'welcome' => Promotion::create([
                'code' => 'WELCOME100',
                'title_en' => '100% Welcome Match Bonus up to $500',
                'title_id' => 'Bonus Sambutan 100% hingga $500',
                'description_en' => 'Double your first USD deposit with a 100% match. 15x rollover on slots and crash games.',
                'description_id' => 'Gandakan deposit USD pertama Anda dengan bonus 100%. Rollover 15x pada slot dan game crash.',
                'promo_type' => 'deposit_match',
                'match_percentage' => 100.00,
                'fixed_bonus_usd' => 0.00,
                'min_deposit_usd' => 20.00,
                'max_bonus_usd' => 500.00,
                'wagering_multiplier' => 15.00,
                'status' => 'active',
            ]),
            'reload' => Promotion::create([
                'code' => 'RELOAD50',
                'title_en' => '50% Weekend Reload Bonus up to $250',
                'title_id' => 'Bonus Reload Akhir Pekan 50% hingga $250',
                'description_en' => 'Top up over the weekend and receive 50% extra bonus balance with 20x rollover.',
                'description_id' => 'Isi ulang saldo di akhir pekan dan dapatkan tambahan 50% saldo bonus dengan rollover 20x.',
                'promo_type' => 'reload_bonus',
                'match_percentage' => 50.00,
                'fixed_bonus_usd' => 0.00,
                'min_deposit_usd' => 25.00,
                'max_bonus_usd' => 250.00,
                'wagering_multiplier' => 20.00,
                'status' => 'active',
            ]),
            'vip_match' => Promotion::create([
                'code' => 'VIPCASH25',
                'title_en' => 'Platinum VIP 25% High-Roller Boost up to $2,500',
                'title_id' => 'Penguat VIP Platinum 25% hingga $2.500',
                'description_en' => 'Dedicated high-roller reload match with reduced 10x rollover requirement for VIP players.',
                'description_id' => 'Bonus reload khusus high-roller dengan rollover ringan 10x untuk pemain VIP.',
                'promo_type' => 'deposit_match',
                'match_percentage' => 25.00,
                'fixed_bonus_usd' => 0.00,
                'min_deposit_usd' => 500.00,
                'max_bonus_usd' => 2500.00,
                'wagering_multiplier' => 10.00,
                'status' => 'active',
            ]),
            'free_chip' => Promotion::create([
                'code' => 'FREECHIP20',
                'title_en' => '$20.00 Aviator & Crash Free Chip Trial',
                'title_id' => 'Chip Gratis $20,00 untuk Uji Coba Game Aviator & Crash',
                'description_en' => 'Test out our instant crash multipliers with zero deposit required. 25x rollover.',
                'description_id' => 'Uji coba pengali instan game crash kami tanpa perlu deposit awal. Rollover 25x.',
                'promo_type' => 'free_chip',
                'match_percentage' => 0.00,
                'fixed_bonus_usd' => 20.00,
                'min_deposit_usd' => 0.00,
                'max_bonus_usd' => 20.00,
                'wagering_multiplier' => 25.00,
                'status' => 'active',
            ]),
        ];

        // 7. Seed 5 Canonical Demo Personas
        $defaultPassword = Hash::make('password');

        // Persona 1: Alex Mercer (player_standard)
        $alex = User::create([
            'name' => 'Alex Mercer',
            'email' => 'alex.mercer@demo.nexusplay.com',
            'password' => $defaultPassword,
            'role' => 'player',
            'persona_key' => 'player_standard',
            'locale' => 'en',
            'kyc_status' => 'verified',
            'account_status' => 'active',
            'vip_tier_id' => $tiers['bronze']->id,
            'vip_points' => 650.00,
            'daily_deposit_limit_usd' => 2000.00,
            'mfa_enabled' => false,
            'last_login_at' => now(),
        ]);

        $alexWallet = Wallet::create([
            'user_id' => $alex->id,
            'currency' => 'USD',
            'cash_balance_usd' => 250.00,
            'bonus_balance_usd' => 50.00,
            'locked_withdrawal_usd' => 0.00,
            'lifetime_deposited_usd' => 500.00,
            'lifetime_withdrawn_usd' => 0.00,
            'lifetime_wagered_usd' => 650.00,
            'lifetime_won_usd' => 400.00,
        ]);

        // Active bonus for Alex: $50 bonus awarded, $1,000 required, $650 completed
        $alexBonus = PlayerBonus::create([
            'user_id' => $alex->id,
            'promotion_id' => $promotions['welcome']->id,
            'bonus_awarded_usd' => 50.00,
            'wagering_required_usd' => 1000.00,
            'wagering_completed_usd' => 650.00,
            'status' => 'active',
        ]);

        // Balanced ledger entries for Alex
        WalletLedgerEntry::create([
            'wallet_id' => $alexWallet->id,
            'user_id' => $alex->id,
            'entry_reference' => 'LED-INIT-ALEX-DEP',
            'idempotency_key' => 'idemp-init-alex-dep',
            'entry_type' => 'deposit_credit',
            'balance_bucket' => 'cash',
            'amount_usd' => 500.00,
            'cash_balance_before_usd' => 0.00,
            'cash_balance_after_usd' => 500.00,
            'bonus_balance_before_usd' => 0.00,
            'bonus_balance_after_usd' => 0.00,
            'locked_balance_before_usd' => 0.00,
            'locked_balance_after_usd' => 0.00,
            'description_en' => 'Initial USDT TRC-20 Deposit',
            'description_id' => 'Deposit Awal USDT TRC-20',
            'actor_user_id' => $alex->id,
            'created_at' => now()->subDays(3),
        ]);

        WalletLedgerEntry::create([
            'wallet_id' => $alexWallet->id,
            'user_id' => $alex->id,
            'entry_reference' => 'LED-INIT-ALEX-BNS',
            'idempotency_key' => 'idemp-init-alex-bns',
            'entry_type' => 'bonus_credit',
            'balance_bucket' => 'bonus',
            'amount_usd' => 50.00,
            'cash_balance_before_usd' => 500.00,
            'cash_balance_after_usd' => 500.00,
            'bonus_balance_before_usd' => 0.00,
            'bonus_balance_after_usd' => 50.00,
            'locked_balance_before_usd' => 0.00,
            'locked_balance_after_usd' => 0.00,
            'description_en' => 'Welcome Deposit Match Bonus 100%',
            'description_id' => 'Bonus Sambutan Deposit Match 100%',
            'actor_user_id' => $alex->id,
            'created_at' => now()->subDays(3),
        ]);

        WalletLedgerEntry::create([
            'wallet_id' => $alexWallet->id,
            'user_id' => $alex->id,
            'entry_reference' => 'LED-INIT-ALEX-BET',
            'idempotency_key' => 'idemp-init-alex-bet',
            'entry_type' => 'game_bet_debit',
            'balance_bucket' => 'cash',
            'amount_usd' => -250.00,
            'cash_balance_before_usd' => 500.00,
            'cash_balance_after_usd' => 250.00,
            'bonus_balance_before_usd' => 50.00,
            'bonus_balance_after_usd' => 50.00,
            'locked_balance_before_usd' => 0.00,
            'locked_balance_after_usd' => 0.00,
            'description_en' => 'Cumulative Session Bets on Gates of Olympus 1000',
            'description_id' => 'Akumulasi Taruhan Sesi pada Gates of Olympus 1000',
            'actor_user_id' => $alex->id,
            'created_at' => now()->subHours(5),
        ]);

        // Persona 2: Victoria Sterling (player_vip)
        $victoria = User::create([
            'name' => 'Victoria Sterling',
            'email' => 'victoria.sterling@demo.nexusplay.com',
            'password' => $defaultPassword,
            'role' => 'player',
            'persona_key' => 'player_vip',
            'locale' => 'en',
            'kyc_status' => 'verified',
            'account_status' => 'active',
            'vip_tier_id' => $tiers['platinum']->id,
            'vip_points' => 78500.00,
            'daily_deposit_limit_usd' => 50000.00,
            'mfa_enabled' => false,
            'last_login_at' => now(),
        ]);

        $victoriaWallet = Wallet::create([
            'user_id' => $victoria->id,
            'currency' => 'USD',
            'cash_balance_usd' => 14850.00,
            'bonus_balance_usd' => 0.00,
            'locked_withdrawal_usd' => 1500.00,
            'lifetime_deposited_usd' => 20000.00,
            'lifetime_withdrawn_usd' => 5000.00,
            'lifetime_wagered_usd' => 78500.00,
            'lifetime_won_usd' => 83350.00,
        ]);

        // 1 Pending High-Value Withdrawal for Victoria ($1,500.00)
        $victoriaWithdrawal = PaymentTransaction::create([
            'user_id' => $victoria->id,
            'payment_method_id' => $paymentMethods['wire']->id,
            'reference' => 'WDR-20261107-900102',
            'idempotency_key' => 'idemp-wdr-vic-900102',
            'type' => 'withdrawal',
            'currency' => 'USD',
            'amount_usd' => 1500.00,
            'fee_usd' => 0.00,
            'net_amount_usd' => 1500.00,
            'status' => 'pending_review',
            'destination_account' => 'Chase Bank Wire #8839-44120-USD',
            'is_high_value' => true,
            'created_at' => now()->subHours(2),
        ]);

        WalletLedgerEntry::create([
            'wallet_id' => $victoriaWallet->id,
            'user_id' => $victoria->id,
            'entry_reference' => 'LED-INIT-VIC-DEP',
            'idempotency_key' => 'idemp-init-vic-dep',
            'entry_type' => 'deposit_credit',
            'balance_bucket' => 'cash',
            'amount_usd' => 16350.00,
            'cash_balance_before_usd' => 0.00,
            'cash_balance_after_usd' => 16350.00,
            'bonus_balance_before_usd' => 0.00,
            'bonus_balance_after_usd' => 0.00,
            'locked_balance_before_usd' => 0.00,
            'locked_balance_after_usd' => 0.00,
            'description_en' => 'Confirmed High-Roller Bank Wire Deposit',
            'description_id' => 'Deposit Transfer Bank High-Roller Terkonfirmasi',
            'actor_user_id' => $victoria->id,
            'created_at' => now()->subDays(5),
        ]);

        WalletLedgerEntry::create([
            'wallet_id' => $victoriaWallet->id,
            'user_id' => $victoria->id,
            'entry_reference' => 'LED-INIT-VIC-LOCK-CASH',
            'idempotency_key' => 'idemp-init-vic-lock-c',
            'entry_type' => 'withdrawal_lock',
            'balance_bucket' => 'cash',
            'amount_usd' => -1500.00,
            'cash_balance_before_usd' => 16350.00,
            'cash_balance_after_usd' => 14850.00,
            'bonus_balance_before_usd' => 0.00,
            'bonus_balance_after_usd' => 0.00,
            'locked_balance_before_usd' => 0.00,
            'locked_balance_after_usd' => 1500.00,
            'related_type' => PaymentTransaction::class,
            'related_id' => $victoriaWithdrawal->id,
            'description_en' => 'Cash Locked for High-Value Withdrawal #WDR-20261107-900102',
            'description_id' => 'Saldo Tunai Ditahan untuk Penarikan High-Value #WDR-20261107-900102',
            'actor_user_id' => $victoria->id,
            'created_at' => now()->subHours(2),
        ]);

        WalletLedgerEntry::create([
            'wallet_id' => $victoriaWallet->id,
            'user_id' => $victoria->id,
            'entry_reference' => 'LED-INIT-VIC-LOCK-HOLD',
            'idempotency_key' => 'idemp-init-vic-lock-h',
            'entry_type' => 'withdrawal_lock',
            'balance_bucket' => 'locked_withdrawal',
            'amount_usd' => 1500.00,
            'cash_balance_before_usd' => 16350.00,
            'cash_balance_after_usd' => 14850.00,
            'bonus_balance_before_usd' => 0.00,
            'bonus_balance_after_usd' => 0.00,
            'locked_balance_before_usd' => 0.00,
            'locked_balance_after_usd' => 1500.00,
            'related_type' => PaymentTransaction::class,
            'related_id' => $victoriaWithdrawal->id,
            'description_en' => 'Hold Credited for High-Value Withdrawal #WDR-20261107-900102',
            'description_id' => 'Dana Tertahan Dikreditkan untuk Penarikan #WDR-20261107-900102',
            'actor_user_id' => $victoria->id,
            'created_at' => now()->subHours(2),
        ]);

        // Persona 3: Marcus Vance (affiliate_partner)
        $marcus = User::create([
            'name' => 'Marcus Vance',
            'email' => 'marcus.vance@demo.nexusplay.com',
            'password' => $defaultPassword,
            'role' => 'affiliate',
            'persona_key' => 'affiliate_partner',
            'locale' => 'en',
            'kyc_status' => 'verified',
            'account_status' => 'active',
            'mfa_enabled' => false,
            'last_login_at' => now(),
        ]);

        $marcusWallet = Wallet::create([
            'user_id' => $marcus->id,
            'currency' => 'USD',
            'cash_balance_usd' => 0.00,
            'bonus_balance_usd' => 0.00,
            'locked_withdrawal_usd' => 0.00,
        ]);

        $marcusProfile = AffiliateProfile::create([
            'user_id' => $marcus->id,
            'referral_code' => 'VANCE30',
            'revshare_percentage' => 30.00,
            'commission_balance_usd' => 1420.50,
            'lifetime_earned_usd' => 2920.50,
            'lifetime_paid_usd' => 1500.00,
            'status' => 'active',
        ]);

        // Persona 4: Nadia Kusuma (ops_finance_staff)
        $nadia = User::create([
            'name' => 'Nadia Kusuma',
            'email' => 'nadia.kusuma@demo.nexusplay.com',
            'password' => $defaultPassword,
            'role' => 'ops_staff',
            'persona_key' => 'ops_finance_staff',
            'locale' => 'en',
            'kyc_status' => 'verified',
            'account_status' => 'active',
            'mfa_enabled' => true,
            'last_login_at' => now(),
        ]);

        Wallet::create([
            'user_id' => $nadia->id,
            'currency' => 'USD',
        ]);

        // Persona 5: Damian Thorne (super_admin)
        $damian = User::create([
            'name' => 'Damian Thorne',
            'email' => 'damian.thorne@demo.nexusplay.com',
            'password' => $defaultPassword,
            'role' => 'super_admin',
            'persona_key' => 'super_admin',
            'locale' => 'en',
            'kyc_status' => 'verified',
            'account_status' => 'active',
            'mfa_enabled' => true,
            'last_login_at' => now(),
        ]);

        Wallet::create([
            'user_id' => $damian->id,
            'currency' => 'USD',
        ]);

        // 8. Background Referred Players for Marcus Vance
        $bgPlayers = [
            ['name' => 'Jordan Lee', 'email' => 'jordan.lee@example.com', 'cash' => 320.00, 'bets' => 2400.00, 'wins' => 1800.00],
            ['name' => 'Sofia Chen', 'email' => 'sofia.chen@example.com', 'cash' => 750.00, 'bets' => 5200.00, 'wins' => 3900.00],
            ['name' => 'Carlos Mendez', 'email' => 'carlos.m@example.com', 'cash' => 120.00, 'bets' => 1600.00, 'wins' => 1100.00],
            ['name' => 'Emma Watson', 'email' => 'emma.w@example.com', 'cash' => 890.00, 'bets' => 6800.00, 'wins' => 5200.00],
            ['name' => 'Liam Brown', 'email' => 'liam.b@example.com', 'cash' => 450.00, 'bets' => 3100.00, 'wins' => 2400.00],
            ['name' => 'Chloe Davis', 'email' => 'chloe.d@example.com', 'cash' => 610.00, 'bets' => 4200.00, 'wins' => 3100.00],
        ];

        foreach ($bgPlayers as $idx => $bg) {
            $p = User::create([
                'name' => $bg['name'],
                'email' => $bg['email'],
                'password' => $defaultPassword,
                'role' => 'player',
                'locale' => 'en',
                'kyc_status' => 'verified',
                'account_status' => 'active',
                'vip_tier_id' => $tiers['silver']->id,
                'vip_points' => $bg['bets'],
                'referred_by_affiliate_id' => $marcusProfile->id,
                'created_at' => now()->subDays(15 + $idx),
            ]);

            $w = Wallet::create([
                'user_id' => $p->id,
                'currency' => 'USD',
                'cash_balance_usd' => $bg['cash'],
                'bonus_balance_usd' => 0.00,
                'locked_withdrawal_usd' => 0.00,
                'lifetime_deposited_usd' => $bg['cash'] + 500,
                'lifetime_wagered_usd' => $bg['bets'],
                'lifetime_won_usd' => $bg['wins'],
            ]);

            WalletLedgerEntry::create([
                'wallet_id' => $w->id,
                'user_id' => $p->id,
                'entry_reference' => 'LED-INIT-BG-' . $p->id,
                'idempotency_key' => 'idemp-init-bg-' . $p->id,
                'entry_type' => 'deposit_credit',
                'balance_bucket' => 'cash',
                'amount_usd' => $bg['cash'],
                'cash_balance_before_usd' => 0.00,
                'cash_balance_after_usd' => $bg['cash'],
                'description_en' => 'Initial Player Deposit',
                'description_id' => 'Deposit Awal Pemain',
                'actor_user_id' => $p->id,
                'created_at' => now()->subDays(10),
            ]);

            $ggr = $bg['bets'] - $bg['wins'];
            $ngr = max(0.00, $ggr * 0.85);
            $commission = round($ngr * 0.30, 2);

            AffiliateReferral::create([
                'affiliate_profile_id' => $marcusProfile->id,
                'referred_user_id' => $p->id,
                'attributed_at' => now()->subDays(15 + $idx),
                'total_bets_usd' => $bg['bets'],
                'total_wins_usd' => $bg['wins'],
                'net_ggr_usd' => $ggr,
                'bonus_deductions_usd' => 0.00,
                'net_ngr_usd' => $ngr,
                'commission_generated_usd' => $commission,
            ]);
        }

        // Standard withdrawal pending review for Alex Mercer ($350.00)
        // Set Alex's locked_withdrawal_usd to $350.00 and adjust cash
        // Let's create an additional small pending withdrawal
        $alexStdWithdrawal = PaymentTransaction::create([
            'user_id' => $alex->id,
            'payment_method_id' => $paymentMethods['usdt']->id,
            'reference' => 'WDR-20261107-100201',
            'idempotency_key' => 'idemp-wdr-alex-100201',
            'type' => 'withdrawal',
            'currency' => 'USD',
            'amount_usd' => 50.00,
            'fee_usd' => 0.00,
            'net_amount_usd' => 50.00,
            'status' => 'pending_review',
            'destination_account' => 'TRX7xL9q4KaM...889UsdtTrc20',
            'is_high_value' => false,
            'created_at' => now()->subMinutes(30),
        ]);

        // Adjust Alex's wallet for this $50 pending withdrawal
        $alexWallet->cash_balance_usd = 200.00;
        $alexWallet->locked_withdrawal_usd = 50.00;
        $alexWallet->save();

        WalletLedgerEntry::create([
            'wallet_id' => $alexWallet->id,
            'user_id' => $alex->id,
            'entry_reference' => 'LED-ALEX-WDR-50-CASH',
            'idempotency_key' => 'idemp-alex-wdr-50-c',
            'entry_type' => 'withdrawal_lock',
            'balance_bucket' => 'cash',
            'amount_usd' => -50.00,
            'cash_balance_before_usd' => 250.00,
            'cash_balance_after_usd' => 200.00,
            'bonus_balance_before_usd' => 50.00,
            'bonus_balance_after_usd' => 50.00,
            'locked_balance_before_usd' => 0.00,
            'locked_balance_after_usd' => 50.00,
            'related_type' => PaymentTransaction::class,
            'related_id' => $alexStdWithdrawal->id,
            'description_en' => 'Cash Locked for Withdrawal #WDR-20261107-100201',
            'description_id' => 'Saldo Tunai Ditahan untuk Penarikan #WDR-20261107-100201',
            'actor_user_id' => $alex->id,
            'created_at' => now()->subMinutes(30),
        ]);

        WalletLedgerEntry::create([
            'wallet_id' => $alexWallet->id,
            'user_id' => $alex->id,
            'entry_reference' => 'LED-ALEX-WDR-50-HOLD',
            'idempotency_key' => 'idemp-alex-wdr-50-h',
            'entry_type' => 'withdrawal_lock',
            'balance_bucket' => 'locked_withdrawal',
            'amount_usd' => 50.00,
            'cash_balance_before_usd' => 250.00,
            'cash_balance_after_usd' => 200.00,
            'bonus_balance_before_usd' => 50.00,
            'bonus_balance_after_usd' => 50.00,
            'locked_balance_before_usd' => 0.00,
            'locked_balance_after_usd' => 50.00,
            'related_type' => PaymentTransaction::class,
            'related_id' => $alexStdWithdrawal->id,
            'description_en' => 'Hold Credited for Withdrawal #WDR-20261107-100201',
            'description_id' => 'Dana Tertahan Dikreditkan untuk Penarikan #WDR-20261107-100201',
            'actor_user_id' => $alex->id,
            'created_at' => now()->subMinutes(30),
        ]);

        // 9. Initial Audit Logs
        $auditLogger = app(\App\Services\AuditLoggerService::class);
        $auditLogger->log(
            'system.seeded',
            $damian,
            SystemSetting::class,
            null,
            'Initial platform seed & interconnected demo dataset provisioned'
        );
        $auditLogger->log(
            'demo.quick_login',
            $alex,
            User::class,
            $alex->id,
            'Seeded demo quick-login event for standard player'
        );
        $auditLogger->log(
            'admin.mfa_verified',
            $damian,
            User::class,
            $damian->id,
            'Super admin MFA verification initial checkpoint'
        );

        // 10. Initial Webhook Log
        SimulatedWebhookLog::create([
            'source_type' => 'payment_gateway',
            'event_name' => 'payment.callback',
            'reference' => 'DEP-20261107-SEED01',
            'idempotency_key' => 'idemp-seed-dep-01',
            'signature_header' => hash_hmac('sha256', 'seed_payload', config('demo.webhook_secret')),
            'signature_valid' => true,
            'processing_outcome' => 'processed',
            'payload_json' => [
                'event' => 'payment.callback',
                'reference' => 'DEP-20261107-SEED01',
                'amount_usd' => 500.00,
                'status' => 'paid',
            ],
            'response_json' => ['acknowledged' => true],
            'created_at' => now()->subDays(3),
        ]);

        // Add favorite game for Alex
        GameFavorite::create([
            'user_id' => $alex->id,
            'game_id' => $games['gates-of-olympus-sim']->id,
        ]);
        GameFavorite::create([
            'user_id' => $alex->id,
            'game_id' => $games['aviator-sky-multiplier']->id,
        ]);
    }
}
