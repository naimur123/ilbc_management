<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Change request (Sept 2026), Section 10 of the redesigned Closure screen:
 * "Closure cannot be completed simply by clicking a Close button. The
 * Closure Team must complete a final checklist" — Billing Verification +
 * Collection Verification. Same data-driven checklist pattern already used
 * for reviewer_checklists / audit_checklists / loading_checklists, keyed
 * directly by request_id since (unlike those three) there is no "closure
 * approval" wrapper row until the moment the request is actually closed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('closure_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->string('check_key', 60);
            $table->string('label', 200);
            $table->boolean('is_checked')->default(false);
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();

            $table->unique(['request_id', 'check_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('closure_checklists');
    }
};
