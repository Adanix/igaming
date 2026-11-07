<?php

use App\Http\Controllers\Admin\AdminMfaController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GameManagementController;
use App\Http\Controllers\Admin\PaymentManagementController;
use App\Http\Controllers\Admin\PlayerManagementController;
use App\Http\Controllers\Admin\PromotionManagementController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Affiliate\AffiliateDashboardController;
use App\Http\Controllers\DemoAuthController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Player\GamePlayController;
use App\Http\Controllers\Player\LobbyController;
use App\Http\Controllers\Player\ProfileController;
use App\Http\Controllers\Player\PromotionController;
use App\Http\Controllers\Player\VipController;
use App\Http\Controllers\Player\WalletController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\Webhook\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public & Entry Routes (P0)
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicPageController::class, 'home'])->name('public.home');
Route::get('/docs', [PublicPageController::class, 'docs'])->name('public.docs');

Route::get('/demo/login', [DemoAuthController::class, 'showLogin'])->name('demo.login');
Route::post('/demo/quick-login', [DemoAuthController::class, 'quickLogin'])->name('demo.quick-login');
Route::post('/demo/reset', [DemoAuthController::class, 'reset'])->name('demo.reset');
Route::post('/logout', [DemoAuthController::class, 'logout'])->name('logout');
Route::post('/locale/switch', [LocaleController::class, 'switch'])->name('locale.switch');

/*
|--------------------------------------------------------------------------
| Simulated Webhook Endpoints (CSRF Exempted)
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/payment/callback', [PaymentWebhookController::class, 'handleCallback'])->name('webhooks.payment.callback');

/*
|--------------------------------------------------------------------------
| Player Experience Routes (Tier B UI)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:player'])->group(function () {
    Route::get('/lobby', [LobbyController::class, 'index'])->name('lobby.index');
    Route::post('/lobby/favorite', [LobbyController::class, 'toggleFavorite'])->name('lobby.favorite');

    Route::get('/play/{slug}', [GamePlayController::class, 'launch'])->name('play.launch');
    Route::post('/play/{slug}/round', [GamePlayController::class, 'round'])->name('play.round');
    Route::post('/play/{slug}/rollback', [GamePlayController::class, 'rollback'])->name('play.rollback');

    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/deposit', [WalletController::class, 'storeDeposit'])->name('wallet.deposit.store');
    Route::get('/wallet/deposit/sandbox/{id}', [WalletController::class, 'showSandbox'])->name('wallet.deposit.sandbox');
    Route::post('/wallet/withdraw', [WalletController::class, 'storeWithdrawal'])->name('wallet.withdraw.store');
    Route::post('/wallet/withdraw/{id}/cancel', [WalletController::class, 'cancelWithdrawal'])->name('wallet.withdraw.cancel');

    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
    Route::post('/promotions/{id}/claim', [PromotionController::class, 'claim'])->name('promotions.claim');
    Route::post('/promotions/{id}/forfeit', [PromotionController::class, 'forfeit'])->name('promotions.forfeit');

    Route::get('/vip', [VipController::class, 'index'])->name('vip.index');
    Route::post('/vip/claim-cashback', [VipController::class, 'claimCashback'])->name('vip.claim-cashback');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| Affiliate Partner Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:affiliate'])->prefix('affiliate')->name('affiliate.')->group(function () {
    Route::get('/', [AffiliateDashboardController::class, 'index'])->name('dashboard');
    Route::post('/recalculate', [AffiliateDashboardController::class, 'recalculate'])->name('recalculate');
    Route::post('/payout', [AffiliateDashboardController::class, 'requestPayout'])->name('payout');
});

/*
|--------------------------------------------------------------------------
| Operations & Backoffice Admin Routes (Tier C UI)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:ops_staff,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    // MFA Challenge & Verification (exempt from admin.mfa check)
    Route::get('/mfa-challenge', [AdminMfaController::class, 'showChallenge'])->name('mfa.challenge');
    Route::post('/mfa-verify', [AdminMfaController::class, 'verifyChallenge'])->name('mfa.verify');

    // MFA-Guarded Administration
    Route::middleware(['admin.mfa'])->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Players Management
        Route::get('/players', [PlayerManagementController::class, 'index'])->name('players.index');
        Route::get('/players/{id}', [PlayerManagementController::class, 'show'])->name('players.show');
        Route::post('/players/{id}/status', [PlayerManagementController::class, 'updateStatus'])->name('players.status');
        Route::post('/players/{id}/kyc', [PlayerManagementController::class, 'updateKyc'])->name('players.kyc');
        Route::post('/players/{id}/adjust-balance', [PlayerManagementController::class, 'adjustBalance'])->name('players.adjust-balance');

        // Games Management
        Route::get('/games', [GameManagementController::class, 'index'])->name('games.index');
        Route::post('/games/{id}/toggle', [GameManagementController::class, 'toggleStatus'])->name('games.toggle');
        Route::post('/games/{id}/limits', [GameManagementController::class, 'updateLimits'])->name('games.limits');

        // Payment Approvals & Cashier Backoffice
        Route::get('/payments', [PaymentManagementController::class, 'index'])->name('payments.index');
        Route::post('/payments/{id}/approve', [PaymentManagementController::class, 'approveWithdrawal'])->name('payments.approve');
        Route::post('/payments/{id}/reject', [PaymentManagementController::class, 'rejectWithdrawal'])->name('payments.reject');

        // Financial & Analytics Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/reconciliation', [ReportController::class, 'reconciliation'])->name('reports.reconciliation');
        Route::get('/reports/export', [ReportController::class, 'exportCsv'])->name('reports.export');

        // Promotions Management
        Route::get('/promotions', [PromotionManagementController::class, 'index'])->name('promotions.index');
        Route::post('/promotions', [PromotionManagementController::class, 'store'])->name('promotions.store');
        Route::post('/promotions/{id}/toggle', [PromotionManagementController::class, 'toggleStatus'])->name('promotions.toggle');

        // Audit Trail & Webhook Telemetry
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');

        // Global System Settings
        Route::get('/settings', [SystemSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SystemSettingController::class, 'update'])->name('settings.update');
    });
});
