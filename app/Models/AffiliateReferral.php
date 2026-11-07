<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateReferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_profile_id',
        'referred_user_id',
        'attributed_at',
        'total_bets_usd',
        'total_wins_usd',
        'net_ggr_usd',
        'bonus_deductions_usd',
        'net_ngr_usd',
        'commission_generated_usd',
    ];

    protected function casts(): array
    {
        return [
            'attributed_at' => 'datetime',
            'total_bets_usd' => 'decimal:2',
            'total_wins_usd' => 'decimal:2',
            'net_ggr_usd' => 'decimal:2',
            'bonus_deductions_usd' => 'decimal:2',
            'net_ngr_usd' => 'decimal:2',
            'commission_generated_usd' => 'decimal:2',
        ];
    }

    public function affiliateProfile(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class);
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }
}
