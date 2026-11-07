<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameProvider;
use App\Models\PaymentMethod;
use App\Models\Promotion;
use App\Models\User;
use App\Models\VipTier;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    /**
     * Render the public landing page (P0-01).
     */
    public function home(): View
    {
        $featuredGames = Game::with('provider')
            ->where('is_featured', true)
            ->where('status', 'active')
            ->limit(8)
            ->get();

        $metrics = [
            'total_games' => Game::where('status', 'active')->count(),
            'total_providers' => GameProvider::where('status', 'active')->count(),
            'total_payment_rails' => PaymentMethod::where('status', 'active')->count(),
            'seeded_personas' => count(config('demo.personas', [])),
            'active_promos' => Promotion::where('status', 'active')->count(),
        ];

        return view('public.home', compact('featuredGames', 'metrics'));
    }

    /**
     * Render the comprehensive feature & API documentation (P0-02).
     */
    public function docs(): View
    {
        $providers = GameProvider::withCount('games')->get();
        $paymentMethods = PaymentMethod::where('status', 'active')->get();
        $vipTiers = VipTier::orderBy('min_wagered_usd')->get();

        return view('public.docs', compact('providers', 'paymentMethods', 'vipTiers'));
    }
}
