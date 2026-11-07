<?php

if (! function_exists('format_usd')) {
    /**
     * Format any monetary amount strictly into USD format: $1,250.00 or -$50.00
     */
    function format_usd($amount, bool $showSign = false): string
    {
        $numeric = (float) $amount;
        $formatted = number_format(abs($numeric), 2, '.', ',');

        if ($numeric < 0) {
            return '-$' . $formatted;
        }

        if ($showSign && $numeric > 0) {
            return '+$' . $formatted;
        }

        return '$' . $formatted;
    }
}

if (! function_exists('current_locale')) {
    /**
     * Get current active UI locale (en or id)
     */
    function current_locale(): string
    {
        return app()->getLocale() ?: 'en';
    }
}

if (! function_exists('is_id_locale')) {
    /**
     * Check if current locale is Indonesian
     */
    function is_id_locale(): bool
    {
        return current_locale() === 'id';
    }
}

if (! function_exists('demo_persona')) {
    /**
     * Get current demo persona config array if authenticated
     */
    function demo_persona(): ?array
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $key = session('demo_persona_key') ?? $user->persona_key;
        if (! $key) {
            return null;
        }

        return config("demo.personas.{$key}");
    }
}
