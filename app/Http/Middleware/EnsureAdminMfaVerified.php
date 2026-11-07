<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminMfaVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('demo.login');
        }

        // Only enforce for administrative staff and super admin
        if (in_array($user->role, ['ops_staff', 'super_admin'], true)) {
            if (! $request->session()->has('admin_mfa_verified_at')) {
                if (! $request->routeIs('admin.mfa.*')) {
                    $request->session()->put('url.intended', $request->fullUrl());
                    return redirect()->route('admin.mfa.challenge');
                }
            }
        }

        return $next($request);
    }
}
