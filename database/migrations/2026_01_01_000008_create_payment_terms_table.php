<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_terms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // "100% Advance", "Net 30", "50% Advance + 50% on Delivery"
            $table->unsignedTinyInteger('advance_percent')->default(0);
            $table->unsignedSmallInteger('credit_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_terms');
    }
};
