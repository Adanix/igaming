<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('demo.login');
        }

        // Support comma separated roles or multiple arguments
        $allowed = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $subRole) {
                $allowed[] = trim($subRole);
            }
        }

        if (! in_array($user->role, $allowed, true)) {
            abort(403, 'Unauthorized access: your role does not have permission to view this section.');
        }

        return $next($request);
    }
}
