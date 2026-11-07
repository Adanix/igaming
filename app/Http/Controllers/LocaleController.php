<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch application language between English ('en') and Indonesian ('id').
     */
    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => 'required|string|in:en,id',
        ]);

        $locale = $validated['locale'];
        session(['locale' => $locale]);

        if (auth()->check()) {
            $user = auth()->user();
            $user->locale = $locale;
            $user->save();
        }

        app()->setLocale($locale);

        return redirect()->back();
    }
}
