<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show player profile and responsible gaming limits.
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load('vipTier', 'wallet');

        return view('player.profile', compact('user'));
    }

    /**
     * Update player profile and deposit limits.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'daily_deposit_limit_usd' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user->name = $validated['name'];
        $user->daily_deposit_limit_usd = $validated['daily_deposit_limit_usd'] ?? null;
        $user->save();

        return redirect()->route('profile.index')
            ->with('status', 'Profile & Responsible Gaming limits updated successfully.');
    }
}
