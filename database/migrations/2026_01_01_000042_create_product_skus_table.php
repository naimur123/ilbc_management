<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_skus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku_code', 60)->unique();
            $table->string('microsoft_sku_id', 60)->nullable();
            $table->string('microsoft_product_id', 60)->nullable();
            $table->string('description', 255)->nullable();
            // SEAT_BASED, CONSUMPTION, ONE_TIME
            $table->string('billing_model', 20)->default('SEAT_BASED');
            $table->string('consumption_unit', 30)->nullable();
            // MONTHLY, ANNUAL, TRIENNIAL, NA
            $table->string('term', 20)->nullable()->default('MONTHLY');
            $table->unsignedInteger('min_seats')->nullable();
            $table->json('billing_cycle_options')->nullable();
            $table->boolean('supports_trial')->default(false);
            $table->boolean('is_renewable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_skus');
    }
};
