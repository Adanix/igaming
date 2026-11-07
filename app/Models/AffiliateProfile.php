<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliateProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'referral_code',
        'revshare_percentage',
        'commission_balance_usd',
        'lifetime_earned_usd',
        'lifetime_paid_usd',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'revshare_percentage' => 'decimal:2',
            'commission_balance_usd' => 'decimal:2',
            'lifetime_earned_usd' => 'decimal:2',
            'lifetime_paid_usd' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(AffiliateReferral::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class);
    }
}
