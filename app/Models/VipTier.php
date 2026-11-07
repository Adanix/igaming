<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VipTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name_en',
        'name_id',
        'min_wagered_usd',
        'cashback_rate_pct',
        'daily_withdrawal_limit_usd',
        'badge_color',
    ];

    protected function casts(): array
    {
        return [
            'min_wagered_usd' => 'decimal:2',
            'cashback_rate_pct' => 'decimal:2',
            'daily_withdrawal_limit_usd' => 'decimal:2',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => app()->getLocale() === 'id' ? $this->name_id : $this->name_en,
        );
    }

    protected function cashbackPercentage(): Attribute
    {
        return Attribute::make(
            get: fn () => (float) $this->cashback_rate_pct,
        );
    }

    protected function levelOrder(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) $this->id,
        );
    }
}
