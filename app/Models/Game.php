<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_provider_id',
        'slug',
        'game_code',
        'title',
        'category',
        'rtp_percentage',
        'volatility',
        'min_bet_usd',
        'max_bet_usd',
        'wagering_contribution_pct',
        'is_featured',
        'status',
        'thumbnail_gradient',
    ];

    protected function casts(): array
    {
        return [
            'rtp_percentage' => 'decimal:2',
            'min_bet_usd' => 'decimal:2',
            'max_bet_usd' => 'decimal:2',
            'wagering_contribution_pct' => 'decimal:2',
            'is_featured' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(GameProvider::class, 'game_provider_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(GameFavorite::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && (! $this->provider || $this->provider->status === 'active');
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->favorites()->where('user_id', $user->id)->exists();
    }
}
