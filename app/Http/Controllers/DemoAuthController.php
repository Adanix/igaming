<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DemoAuthController extends Controller
{
    public function __construct(
        protected AuditLoggerService $auditLogger
    ) {}

    /**
     * Render the 5-persona selection page (P0-03).
     */
    public function showLogin(): View
    {
        $personasConfig = config('demo.personas', []);
        $personas = [];

        foreach ($personasConfig as $key => $config) {
            $user = User::where('email', $config['email'])->with('wallet', 'vipTier')->first();
            $personas[$key] = [
                'config' => $config,
                'user' => $user,
                'wallet' => $user?->wallet,
            ];
        }

        return view('demo.login', compact('personas'));
    }

    /**
     * Authenticate an allowlisted demo persona with 1 click (P0-04).
     */
    public function quickLogin(Request $request): RedirectResponse
    {
        $allowedPersonas = array_keys(config('demo.personas', []));

        $validated = $request->validate([
            'persona_key' => ['required', 'string', Rule::in($allowedPersonas)],
            'skip_mfa' => ['nullable', 'boolean'],
        ]);

        $personaKey = $validated['persona_key'];
        $personaConfig = config("demo.personas.{$personaKey}");

        $user = User::where('email', $personaConfig['email'])->firstOrFail();

        Auth::login($user, false);
        $request->session()->regenerate();
        $request->session()->put('demo_persona_key', $personaKey);

        // For admin personas: check if user wants to enter directly or test MFA challenge
        if (in_array($user->role, ['ops_staff', 'super_admin'], true)) {
            if ($request->boolean('skip_mfa', true)) {
                $request->session()->put('admin_mfa_verified_at', now()->toIso8601String());
            } else {
                $request->session()->forget('admin_mfa_verified_at');
            }
        }

        $this->auditLogger->log(
            'demo.quick_login',
            $user,
            User::class,
            $user->id,
            "Quick-login executed for demo persona: {$personaKey}"
        );

        $targetRoute = $personaConfig['landing_route'] ?? 'lobby.index';

        return redirect()->route($targetRoute);
    }

    /**
     * Log out and invalidate session (P0-05).
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            $this->auditLogger->log(
                'demo.logout',
                $user,
                User::class,
                $user->id,
                'User session terminated via logout'
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (config('demo.enabled', true)) {
            return redirect()->route('demo.login');
        }

        return redirect()->route('public.home');
    }

    /**
     * Reset demo database to canonical seed state (P0-06 / Section 22.2).
     */
    public function reset(Request $request): RedirectResponse
    {
        if (config('database.default') === 'sqlite') {
            \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
            \App\Models\AuditLog::truncate();
            \App\Models\SimulatedWebhookLog::truncate();
            \App\Models\WalletLedgerEntry::truncate();
            \App\Models\PaymentTransaction::truncate();
            \App\Models\PlayerBonus::truncate();
            \App\Models\GameRound::truncate();
            \App\Models\GameSession::truncate();
            \App\Models\GameFavorite::truncate();
            \App\Models\AffiliateCommission::truncate();
            \App\Models\AffiliateReferral::truncate();
            \App\Models\AffiliateProfile::truncate();
            \App\Models\Wallet::truncate();
            \App\Models\Game::truncate();
            \App\Models\GameProvider::truncate();
            \App\Models\PaymentMethod::truncate();
            \App\Models\Promotion::truncate();
            \App\Models\SystemSetting::truncate();
            \App\Models\User::truncate();
            \App\Models\VipTier::truncate();
            \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

            \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        } else {
            \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
                '--seed' => true,
                '--force' => true,
            ]);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('demo.login')->with('status', 'Platform database has been completely restored to canonical seed state.');
    }
}
