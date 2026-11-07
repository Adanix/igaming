<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'persona_key',
        'locale',
        'kyc_status',
        'account_status',
        'vip_tier_id',
        'vip_points',
        'daily_deposit_limit_usd',
        'referred_by_affiliate_id',
        'mfa_enabled',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'mfa_enabled' => 'boolean',
            'vip_points' => 'decimal:2',
            'daily_deposit_limit_usd' => 'decimal:2',
        ];
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function vipTier(): BelongsTo
    {
        return $this->belongsTo(VipTier::class, 'vip_tier_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(WalletLedgerEntry::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    public function gameRounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    public function bonuses(): HasMany
    {
        return $this->hasMany(PlayerBonus::class);
    }

    public function activeBonus(): HasOne
    {
        return $this->hasOne(PlayerBonus::class)->where('status', 'active');
    }

    public function affiliateProfile(): HasOne
    {
        return $this->hasOne(AffiliateProfile::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(GameFavorite::class);
    }

    public function isPlayer(): bool
    {
        return $this->role === 'player';
    }

    public function isAffiliate(): bool
    {
        return $this->role === 'affiliate';
    }

    public function isOpsStaff(): bool
    {
        return $this->role === 'ops_staff';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            $roles = explode(',', $roles);
        }

        $roles = array_map('trim', $roles);
        return in_array($this->role, $roles, true);
    }
}
