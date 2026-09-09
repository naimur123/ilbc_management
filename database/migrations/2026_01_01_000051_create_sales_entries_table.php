<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 1:1 extension of `requests` holding the Sales-Entry-specific fields
 * (Section 9's "Payment Information" group + uploads) that don't belong
 * on the generic workflow header.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->unique()->constrained('requests')->cascadeOnDelete();
            $table->string('contact_person', 150)->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('source_lead', 100)->nullable();
            $table->string('customer_type', 50)->nullable();
            $table->foreignId('payment_terms_id')->nullable()->constrained('payment_terms')->nullOnDelete();
            $table->unsignedTinyInteger('advance_percent')->default(0);
            $table->unsignedSmallInteger('credit_days')->default(0);
            $table->string('billing_cycle', 30)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_entries');
    }
};
