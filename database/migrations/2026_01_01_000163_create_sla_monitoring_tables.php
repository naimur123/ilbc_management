<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Change request (Sept 2026): a real SLA monitoring & reminder module,
 * placed right after Loading / Installation, on top of the app's existing
 * lightweight per-stage aging check (sla_rules/SLAService, still used by
 * the Reports > SLA/Aging Report — untouched by this migration).
 *
 * `sla_configurations` replaces the two tables the user's spec calls
 * "sla_definitions" and "sla_rules" with one: an admin-defined matching
 * rule (by product / category / customer / department / priority) that
 * IS the reusable definition once it matches. `request_slas` is one
 * concrete SLA commitment per request (a request may have more than one,
 * e.g. an Activation SLA and a separate Billing Submission SLA later —
 * this is exactly why it's its own table and not columns on `requests`).
 * `sla_status_history` is the audit trail of status changes. Escalation
 * and waiver/exception data live directly on `request_slas`
 * (escalated_at/escalated_to_user_id, waived_*) rather than in three more
 * separate tables (sla_reminders/sla_escalations/sla_exceptions) — same
 * information, far less schema for a first release; reminder de-duping
 * uses `reminder_sent_at` the same way.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('sla_type', 30)->default('ACTIVATION'); // ACTIVATION|DELIVERY|RESPONSE|RESOLUTION|CUSTOM
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('priority', 20)->nullable(); // LOW|NORMAL|HIGH|CRITICAL, null = any priority
            $table->unsignedInteger('duration_minutes'); // e.g. 240 = 4 hours, 4320 = 3 days
            $table->unsignedInteger('reminder_before_minutes')->nullable(); // also doubles as the "Due Soon" threshold
            $table->unsignedInteger('escalate_after_minutes')->nullable(); // minutes PAST deadline before escalating
            $table->string('responsible_team', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('request_slas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('request_item_id')->nullable()->constrained('request_items')->nullOnDelete();
            $table->foreignId('sla_configuration_id')->nullable()->constrained('sla_configurations')->nullOnDelete();
            $table->string('sla_type', 30);
            $table->string('priority', 20)->default('NORMAL');
            $table->dateTime('start_at');
            $table->dateTime('target_at'); // = start_at + duration_minutes, computed once at creation
            $table->unsignedInteger('duration_minutes');
            $table->unsignedInteger('reminder_before_minutes')->nullable();
            $table->unsignedInteger('escalate_after_minutes')->nullable();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('responsible_team', 100)->nullable();
            $table->foreignId('escalated_to_user_id')->nullable()->constrained('users')->nullOnDelete();

            // PENDING|ACTIVE|DUE_SOON|OVERDUE|COMPLETED_WITHIN_SLA|COMPLETED_LATE|WAIVED
            $table->string('status', 30)->default('ACTIVE');

            $table->dateTime('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();

            $table->text('waived_reason')->nullable();
            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('waived_at')->nullable();

            $table->dateTime('reminder_sent_at')->nullable();
            $table->dateTime('escalated_at')->nullable();

            $table->string('attachment_path', 255)->nullable();
            $table->string('attachment_original_name', 255)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['target_at']);
        });

        Schema::create('sla_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_sla_id')->constrained('request_slas')->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete(); // null = system
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_status_history');
        Schema::dropIfExists('request_slas');
        Schema::dropIfExists('sla_configurations');
    }
};
