<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Simulators\SimulatedMfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMfaController extends Controller
{
    public function __construct(
        protected SimulatedMfaService $mfaService
    ) {}

    /**
     * Show the TOTP MFA challenge screen.
     */
    public function showChallenge(): View
    {
        return view('admin.mfa-challenge');
    }

    /**
     * Verify the entered TOTP code (demo code: 123456).
     */
    public function verifyChallenge(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if ($this->mfaService->verifyCode($user, $validated['code'])) {
            $request->session()->put('admin_mfa_verified_at', now()->toIso8601String());

            return redirect()->intended(route('admin.dashboard'))
                ->with('status', 'Multi-Factor Authentication verified successfully.');
        }

        return redirect()->back()->withErrors([
            'code' => 'Invalid authentication code. For demo access, please use 123456.',
        ]);
    }
}
