<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameProvider;
use App\Services\AuditLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameManagementController extends Controller
{
    public function __construct(
        protected AuditLoggerService $auditLogger
    ) {}

    /**
     * Display Game Catalog & Provider Management.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $providerFilter = $request->query('provider');

        $query = Game::with('provider');

        if ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('game_code', 'like', "%{$search}%");
        }

        if ($providerFilter) {
            $query->where('game_provider_id', $providerFilter);
        }

        $games = $query->orderBy('title')->paginate(16)->withQueryString();
        $providers = GameProvider::all();

        return view('admin.games.index', compact('games', 'providers', 'search', 'providerFilter'));
    }

    /**
     * Toggle active/maintenance state of a game.
     */
    public function toggleStatus(int $id, Request $request): RedirectResponse
    {
        $game = Game::findOrFail($id);
        $newStatus = $game->status === 'active' ? 'maintenance' : 'active';
        $game->status = $newStatus;
        $game->save();

        $this->auditLogger->log(
            'game.toggle_status',
            $request->user(),
            Game::class,
            $game->id,
            "Toggled game '{$game->title}' status to {$newStatus}"
        );

        return redirect()->back()
            ->with('status', "Game '{$game->title}' status changed to {$newStatus}.");
    }

    /**
     * Update game min/max betting limits and RTP.
     */
    public function updateLimits(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'min_bet_usd' => ['required', 'numeric', 'min:0.01'],
            'max_bet_usd' => ['required', 'numeric', 'gte:min_bet_usd'],
            'rtp_percentage' => ['required', 'numeric', 'min:80', 'max:99.99'],
        ]);

        $game = Game::findOrFail($id);
        $game->min_bet_usd = $validated['min_bet_usd'];
        $game->max_bet_usd = $validated['max_bet_usd'];
        $game->rtp_percentage = $validated['rtp_percentage'];
        $game->save();

        $this->auditLogger->log(
            'game.update_limits',
            $request->user(),
            Game::class,
            $game->id,
            "Updated limits for '{$game->title}': Min $" . number_format($game->min_bet_usd, 2) . ", Max $" . number_format($game->max_bet_usd, 2) . ", RTP {$game->rtp_percentage}%"
        );

        return redirect()->back()
            ->with('status', "Limits updated for game '{$game->title}'.");
    }
}
