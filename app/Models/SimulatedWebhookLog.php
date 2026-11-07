<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimulatedWebhookLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'source_type',
        'event_name',
        'reference',
        'idempotency_key',
        'signature_header',
        'signature_valid',
        'processing_outcome',
        'payload_json',
        'response_json',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'signature_valid' => 'boolean',
            'payload_json' => 'array',
            'response_json' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
