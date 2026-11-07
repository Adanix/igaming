<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulated_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('source_type', ['payment_gateway', 'game_aggregator'])->index();
            $table->string('event_name', 80);
            $table->string('reference', 80)->index();
            $table->string('idempotency_key', 100);
            $table->string('signature_header', 128)->nullable();
            $table->boolean('signature_valid')->default(false);
            $table->enum('processing_outcome', ['processed', 'duplicate_replay_blocked', 'rejected_invalid_signature', 'failed_validation'])->index();
            $table->json('payload_json');
            $table->json('response_json')->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulated_webhook_logs');
    }
};
