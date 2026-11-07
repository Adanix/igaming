<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title_en',
        'title_id',
        'description_en',
        'description_id',
        'promo_type',
        'match_percentage',
        'fixed_bonus_usd',
        'min_deposit_usd',
        'max_bonus_usd',
        'wagering_multiplier',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'match_percentage' => 'decimal:2',
            'fixed_bonus_usd' => 'decimal:2',
            'min_deposit_usd' => 'decimal:2',
            'max_bonus_usd' => 'decimal:2',
            'wagering_multiplier' => 'decimal:2',
        ];
    }

    public function playerBonuses(): HasMany
    {
        return $this->hasMany(PlayerBonus::class);
    }

    public function title(): string
    {
        return app()->getLocale() === 'id' ? $this->title_id : $this->title_en;
    }

    public function description(): string
    {
        return app()->getLocale() === 'id' ? $this->description_id : $this->description_en;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
