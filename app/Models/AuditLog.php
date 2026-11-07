<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'actor_user_id',
        'actor_role',
        'event_action',
        'target_type',
        'target_id',
        'reason',
        'old_values_json',
        'new_values_json',
        'ip_address',
        'integrity_hash',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values_json' => 'array',
            'new_values_json' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    protected function action(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->event_action,
            set: fn ($val) => ['event_action' => $val],
        );
    }

    protected function summary(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->reason,
            set: fn ($val) => ['reason' => $val],
        );
    }

    protected function previousHash(): Attribute
    {
        return Attribute::make(
            get: fn () => null,
        );
    }
}
