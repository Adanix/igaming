<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('title_en', 120);
            $table->string('title_id', 120);
            $table->text('description_en');
            $table->text('description_id');
            $table->enum('promo_type', ['deposit_match', 'reload_bonus', 'free_chip'])->index();
            $table->decimal('match_percentage', 5, 2)->default(100.00);
            $table->decimal('fixed_bonus_usd', 18, 2)->default(0.00);
            $table->decimal('min_deposit_usd', 18, 2)->default(20.00);
            $table->decimal('max_bonus_usd', 18, 2)->default(500.00);
            $table->decimal('wagering_multiplier', 5, 2)->default(15.00);
            $table->enum('status', ['active', 'inactive'])->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
