<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category_focus',
        'status',
        'aggregator_endpoint_sim',
    ];

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
