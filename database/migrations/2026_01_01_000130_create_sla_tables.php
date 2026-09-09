<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_rules', function (Blueprint $table) {
            $table->id();
            $table->string('stage_key', 40)->unique(); // BILLING_CLEARANCE, REVIEWER, LOADING, AUDIT, BILLING, CLOSURE
            $table->string('label', 100);
            $table->unsignedInteger('sla_hours');
            $table->unsignedInteger('due_soon_threshold_hours')->default(1); // "due soon" = within this many hours of breach
            $table->timestamps();
        });

        Schema::create('escalations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->string('stage_key', 40);
            $table->timestamp('escalated_at');
            $table->foreignId('escalated_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escalations');
        Schema::dropIfExists('sla_rules');
    }
};
