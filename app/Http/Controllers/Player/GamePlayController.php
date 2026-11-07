<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameRound;
use App\Services\Simulators\SimulatedGameAggregatorService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GamePlayController extends Controller
{
    public function __construct(
        protected SimulatedGameAggregatorService $aggregator
    ) {}

    /**
     * Launch the interactive game stage screen.
     */
    public function launch(string $slug, Request $request): View|RedirectResponse
    {
        $game = Game::with('provider')
            ->where('game_code', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

        if (! $game->isActive()) {
            return redirect()->route('lobby.index')
                ->with('error', "Game '{$game->title}' is currently unavailable.");
        }

        $user = $request->user();
        $session = $this->aggregator->getOrCreateSession($user, $game);
        $wallet = $user->wallet;
        $activeBonus = $user->activeBonus;

        $recentRounds = GameRound::where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->latest('id')
            ->limit(8)
            ->get();

        return view('player.play', compact('game', 'session', 'wallet', 'activeBonus', 'recentRounds'));
    }

    /**
     * Execute a simulated round with deterministic outcome triggers.
     */
    public function round(string $slug, Request $request): JsonResponse|RedirectResponse
    {
        $game = Game::where('game_code', $slug)->orWhere('id', $slug)->firstOrFail();
        $user = $request->user();

        $validated = $request->validate([
            'bet_amount' => ['required', 'numeric', 'min:0.10', 'max:5000.00'],
            'outcome_mode' => ['nullable', 'string', 'in:random,loss,win_small,win_big,rollback'],
        ]);

        $betAmount = (float) $validated['bet_amount'];
        $outcomeMode = $validated['outcome_mode'] ?? 'random';

        try {
            $result = $this->aggregator->playRound($game, $user, $betAmount, $outcomeMode);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $result,
                ]);
            }

            return redirect()->route('play.launch', $game->game_code)
                ->with('round_result', $result);
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 422);
            }

            return redirect()->route('play.launch', $game->game_code)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Explicitly trigger an atomic round rollback simulation.
     */
    public function rollback(string $slug, Request $request): JsonResponse|RedirectResponse
    {
        $game = Game::where('game_code', $slug)->orWhere('id', $slug)->firstOrFail();
        $user = $request->user();

        $validated = $request->validate([
            'bet_amount' => ['required', 'numeric', 'min:0.10'],
        ]);

        try {
            $result = $this->aggregator->playRound($game, $user, (float) $validated['bet_amount'], 'rollback');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Aggregator atomic rollback executed successfully.',
                    'data' => $result,
                ]);
            }

            return redirect()->route('play.launch', $game->game_code)
                ->with('round_result', $result)
                ->with('status', 'Round bet atomically rolled back to player wallet.');
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
