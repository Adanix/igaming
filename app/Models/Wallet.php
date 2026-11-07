<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'currency',
        'cash_balance_usd',
        'bonus_balance_usd',
        'locked_withdrawal_usd',
        'lifetime_deposited_usd',
        'lifetime_withdrawn_usd',
        'lifetime_wagered_usd',
        'lifetime_won_usd',
    ];

    protected function casts(): array
    {
        return [
            'cash_balance_usd' => 'decimal:2',
            'bonus_balance_usd' => 'decimal:2',
            'locked_withdrawal_usd' => 'decimal:2',
            'lifetime_deposited_usd' => 'decimal:2',
            'lifetime_withdrawn_usd' => 'decimal:2',
            'lifetime_wagered_usd' => 'decimal:2',
            'lifetime_won_usd' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(WalletLedgerEntry::class);
    }

    public function totalPlayableUsd(): float
    {
        return (float) ($this->cash_balance_usd + $this->bonus_balance_usd);
    }

    public function totalAccountEquityUsd(): float
    {
        return (float) ($this->cash_balance_usd + $this->bonus_balance_usd + $this->locked_withdrawal_usd);
    }
}
