<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_method_id',
        'promotion_id',
        'reference',
        'idempotency_key',
        'type',
        'currency',
        'amount_usd',
        'fee_usd',
        'net_amount_usd',
        'status',
        'destination_account',
        'is_high_value',
        'reviewed_by_user_id',
        'review_note',
        'reviewed_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:2',
            'fee_usd' => 'decimal:2',
            'net_amount_usd' => 'decimal:2',
            'is_high_value' => 'boolean',
            'reviewed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PaymentTransaction $tx) {
            if ($tx->fee_usd === null) {
                $tx->fee_usd = 0.00;
            }
            if ($tx->net_amount_usd === null) {
                $tx->net_amount_usd = max(0.00, (float) $tx->amount_usd - (float) $tx->fee_usd);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function playerBonus(): HasOne
    {
        return $this->hasOne(PlayerBonus::class);
    }

    public function isPendingReview(): bool
    {
        return in_array($this->status, ['pending', 'pending_review'], true);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['paid', 'completed'], true);
    }

    protected function direction(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type,
            set: fn ($value) => ['type' => $value],
        );
    }
}
