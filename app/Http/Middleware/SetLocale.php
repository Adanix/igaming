<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('demo.supported_locales', ['en', 'id']);
        $locale = session('locale');

        if (! $locale && auth()->check() && auth()->user()->locale) {
            $locale = auth()->user()->locale;
        }

        if (! $locale || ! in_array($locale, $supported, true)) {
            $locale = config('demo.default_locale', 'en');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
