<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletLedgerEntry extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'wallet_id',
        'user_id',
        'entry_reference',
        'idempotency_key',
        'entry_type',
        'balance_bucket',
        'amount_usd',
        'cash_balance_before_usd',
        'cash_balance_after_usd',
        'bonus_balance_before_usd',
        'bonus_balance_after_usd',
        'locked_balance_before_usd',
        'locked_balance_after_usd',
        'related_type',
        'related_id',
        'description_en',
        'description_id',
        'actor_user_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:2',
            'cash_balance_before_usd' => 'decimal:2',
            'cash_balance_after_usd' => 'decimal:2',
            'bonus_balance_before_usd' => 'decimal:2',
            'bonus_balance_after_usd' => 'decimal:2',
            'locked_balance_before_usd' => 'decimal:2',
            'locked_balance_after_usd' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function description(): string
    {
        return app()->getLocale() === 'id' ? $this->description_id : $this->description_en;
    }
}
