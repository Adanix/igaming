<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'promotion_id',
        'payment_transaction_id',
        'bonus_awarded_usd',
        'wagering_required_usd',
        'wagering_completed_usd',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'bonus_awarded_usd' => 'decimal:2',
            'wagering_required_usd' => 'decimal:2',
            'wagering_completed_usd' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    public function progressPercentage(): float
    {
        if ($this->wagering_required_usd <= 0) {
            return 100.0;
        }

        return min(100.0, round(($this->wagering_completed_usd / $this->wagering_required_usd) * 100, 1));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
