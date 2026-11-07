<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_en',
        'name_id',
        'channel_type',
        'supports_deposit',
        'supports_withdrawal',
        'min_deposit_usd',
        'max_deposit_usd',
        'min_withdrawal_usd',
        'max_withdrawal_usd',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'supports_deposit' => 'boolean',
            'supports_withdrawal' => 'boolean',
            'min_deposit_usd' => 'decimal:2',
            'max_deposit_usd' => 'decimal:2',
            'min_withdrawal_usd' => 'decimal:2',
            'max_withdrawal_usd' => 'decimal:2',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => app()->getLocale() === 'id' ? $this->name_id : $this->name_en,
        );
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
