<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_session_id',
        'user_id',
        'game_id',
        'round_code',
        'idempotency_key',
        'bet_amount_usd',
        'cash_bet_component_usd',
        'bonus_bet_component_usd',
        'payout_amount_usd',
        'multiplier',
        'outcome_type',
        'status',
        'provider_signature',
        'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'bet_amount_usd' => 'decimal:2',
            'cash_bet_component_usd' => 'decimal:2',
            'bonus_bet_component_usd' => 'decimal:2',
            'payout_amount_usd' => 'decimal:2',
            'multiplier' => 'decimal:2',
            'settled_at' => 'datetime',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'game_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function netResultUsd(): float
    {
        return (float) ($this->payout_amount_usd - $this->bet_amount_usd);
    }
}
