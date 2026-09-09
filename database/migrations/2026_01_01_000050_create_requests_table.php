<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The master workflow record every module hangs off (Section 2/28/29).
 * request_items/sales_entries/billing_clearances/reviewer_approvals/
 * loading_records/audit_records/invoices/closures all key on this row's id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no', 30)->unique(); // e.g. REQ-2609-0291, auto-generated
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('salesperson_id')->constrained('salespersons');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('work_order_no', 60)->nullable();
            $table->string('po_number', 60)->nullable();
            $table->date('order_date')->nullable();

            // Current pipeline position, drives menu buckets (Section 8) and Rules 1-5 (Section 40)
            $table->string('status', 40)->default('DRAFT');
            /*
             * DRAFT, SUBMITTED,
             * BILLING_CLEARANCE_PENDING, BILLING_CLEARED, BILLING_REJECTED, BILLING_HOLD,
             * REVIEWER_PENDING, REVIEWER_APPROVED, REVIEWER_RETURNED,
             * LOADING_PENDING, LOADING_IN_PROGRESS, LOADING_COMPLETED,
             * AUDIT_PENDING, AUDIT_RETURNED, AUDIT_APPROVED,
             * BILLING_PENDING, INVOICE_GENERATED, INVOICE_SENT, BILLING_DONE,
             * READY_FOR_CLOSURE, CLOSED, CANCELLED, REOPENED
             */
            $table->string('current_stage', 30)->default('SALES'); // SALES|BILLING_CLEARANCE|REVIEWER|LOADING|AUDIT|BILLING|CLOSURE|CLOSED

            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('submitted_at')->nullable();

            // SLA tracking (Section 34): when the current stage started, so SLAService can compute due/overdue
            $table->timestamp('current_stage_started_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'current_stage']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
