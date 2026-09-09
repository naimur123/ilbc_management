<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Generic, data-driven workflow definition (Section 3: "do NOT hard-code
 * individual names into the workflow"). The default 8-stage flow is
 * seeded as data (WorkflowDefinitionSeeder), not hard-coded in PHP —
 * WorkflowService reads workflow_steps to know ordering/required
 * role/SLA per stage, and workflow_actions is the source of the visual
 * timeline (Section 29).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_definition_id')->constrained('workflow_definitions')->cascadeOnDelete();
            $table->string('step_key', 40); // SALES, BILLING_CLEARANCE, REVIEWER, LOADING, AUDIT, BILLING, CLOSURE
            $table->string('name', 100);
            $table->unsignedInteger('step_order');
            $table->string('required_permission', 100)->nullable();
            $table->unsignedInteger('sla_hours')->default(24);
            $table->timestamps();

            $table->unique(['workflow_definition_id', 'step_key']);
        });

        Schema::create('workflow_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('workflow_definition_id')->constrained('workflow_definitions');
            $table->foreignId('current_step_id')->nullable()->constrained('workflow_steps')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('workflow_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_instance_id')->constrained('workflow_instances')->cascadeOnDelete();
            $table->foreignId('workflow_step_id')->constrained('workflow_steps');
            $table->string('action', 30); // SUBMIT|APPROVE|RETURN|REJECT|HOLD|COMPLETE
            $table->foreignId('performed_by')->constrained('users');
            $table->text('remarks')->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_actions');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflow_definitions');
    }
};
