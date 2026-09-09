<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Post-audit billing stage tracking (Section 25) — distinct from the
 * Billing Pre-Check in billing_clearances. One row per request.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->unique()->constrained('requests')->cascadeOnDelete();
            $table->timestamp('invoice_generated_at')->nullable();
            $table->foreignId('invoice_generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('invoice_sent_at')->nullable();
            $table->foreignId('invoice_sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('billing_done_at')->nullable();
            $table->foreignId('billing_done_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_records');
    }
};
