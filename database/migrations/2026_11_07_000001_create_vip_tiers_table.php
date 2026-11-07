<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vip_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 40)->unique();
            $table->string('name_en', 80);
            $table->string('name_id', 80);
            $table->decimal('min_wagered_usd', 18, 2)->default(0.00);
            $table->decimal('cashback_rate_pct', 5, 2)->default(0.00);
            $table->decimal('daily_withdrawal_limit_usd', 18, 2)->default(5000.00);
            $table->string('badge_color', 40)->default('slate');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_tiers');
    }
};
