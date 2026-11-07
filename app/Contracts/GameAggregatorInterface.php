<?php

namespace App\Contracts;

use App\Models\Game;
use App\Models\GameSession;
use App\Models\User;

interface GameAggregatorInterface
{
    public function getOrCreateSession(User $user, Game $game): GameSession;

    public function playRound(Game $game, User $user, float $betAmount, string $outcomeMode = 'random'): array;
}
