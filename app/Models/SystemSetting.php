<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'value_type',
        'description_en',
        'description_id',
        'updated_by_user_id',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public static function get(string $key, $default = null)
    {
        $setting = static::where('setting_key', $key)->first();
        if (! $setting) {
            return $default;
        }

        return match ($setting->value_type) {
            'boolean' => filter_var($setting->setting_value, FILTER_VALIDATE_BOOLEAN),
            'decimal' => (float) $setting->setting_value,
            'json' => json_decode($setting->setting_value, true),
            default => $setting->setting_value,
        };
    }

    public static function set(string $key, $value, ?int $userId = null): void
    {
        $setting = static::where('setting_key', $key)->first();
        if (! $setting) {
            return;
        }

        $strValue = is_array($value) ? json_encode($value) : (string) $value;
        $setting->update([
            'setting_value' => $strValue,
            'updated_by_user_id' => $userId,
        ]);
    }
}
