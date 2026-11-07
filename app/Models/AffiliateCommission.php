<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_profile_id',
        'period_label',
        'gross_ggr_usd',
        'net_ngr_usd',
        'revshare_pct',
        'commission_amount_usd',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'gross_ggr_usd' => 'decimal:2',
            'net_ngr_usd' => 'decimal:2',
            'revshare_pct' => 'decimal:2',
            'commission_amount_usd' => 'decimal:2',
        ];
    }

    public function affiliateProfile(): BelongsTo
    {
        return $this->belongsTo(AffiliateProfile::class);
    }
}
