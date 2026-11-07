<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameFavorite;
use App\Models\GameProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LobbyController extends Controller
{
    /**
     * Display the Player Game Lobby (P1).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $category = $request->query('category', 'all');
        $providerSlug = $request->query('provider');
        $search = $request->query('q');
        $onlyFavorites = $request->boolean('favorites');

        $query = Game::with('provider')->where('status', 'active');

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($providerSlug) {
            $query->whereHas('provider', function ($q) use ($providerSlug) {
                $q->where('code', $providerSlug);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('game_code', 'like', "%{$search}%");
            });
        }

        if ($onlyFavorites && $user) {
            $favoriteGameIds = GameFavorite::where('user_id', $user->id)->pluck('game_id')->toArray();
            $query->whereIn('id', $favoriteGameIds);
        }

        $games = $query->orderBy('is_featured', 'desc')->orderBy('title')->get();

        $providers = GameProvider::where('status', 'active')->withCount('games')->get();

        $featuredGames = Game::with('provider')
            ->where('is_featured', true)
            ->where('status', 'active')
            ->limit(4)
            ->get();

        $userFavorites = $user
            ? GameFavorite::where('user_id', $user->id)->pluck('game_id')->toArray()
            : [];

        $categories = [
            'all' => 'All Games',
            'slots' => 'Slots',
            'live' => 'Live Dealer',
            'crash' => 'Crash',
            'table' => 'Table Games',
        ];

        return view('player.lobby', compact(
            'games',
            'providers',
            'featuredGames',
            'userFavorites',
            'categories',
            'category',
            'providerSlug',
            'search',
            'onlyFavorites'
        ));
    }

    /**
     * Toggle a game's favorite status for the authenticated player.
     */
    public function toggleFavorite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'game_id' => ['required', 'exists:games,id'],
        ]);

        $userId = $request->user()->id;
        $gameId = $validated['game_id'];

        $existing = GameFavorite::where('user_id', $userId)
            ->where('game_id', $gameId)
            ->first();

        if ($existing) {
            $existing->delete();
            $isFavorite = false;
        } else {
            GameFavorite::create([
                'user_id' => $userId,
                'game_id' => $gameId,
            ]);
            $isFavorite = true;
        }

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite,
            'game_id' => $gameId,
        ]);
    }
}
