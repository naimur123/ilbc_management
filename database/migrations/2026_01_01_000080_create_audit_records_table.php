<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Auditor's decision per request_item (Section 21-24), compared against
 * the Reviewer-approved snapshot + actual loading via audit_variances.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_item_id')->constrained('request_items')->cascadeOnDelete();
            $table->foreignId('loading_record_id')->constrained('loading_records');

            $table->string('decision', 20)->nullable(); // APPROVE|RETURN|HOLD
            $table->text('remarks')->nullable();
            $table->boolean('has_variance')->default(false);
            $table->foreignId('audited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('audited_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_records');
    }
};
