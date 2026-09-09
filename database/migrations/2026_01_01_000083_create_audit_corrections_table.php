<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_record_id')->constrained('audit_records')->cascadeOnDelete();
            $table->string('category', 40);
            // WRONG_PRODUCT|WRONG_SKU|WRONG_QUANTITY|WRONG_VENDOR|WRONG_TENANT|WRONG_PRICE|
            // COST_MISMATCH|DATE_MISMATCH|MISSING_PROOF|DUPLICATE_LOADING|INCOMPLETE_LOADING|OTHER
            $table->text('remarks');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_corrections');
    }
};
