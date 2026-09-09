<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reviewer's overall decision on a request (Section 15-17), and the
 * Cost Snapshot Requirement (Section 41): once approved, the commercial
 * numbers here are frozen and never recalculated even if vendor prices
 * or selling prices change later.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviewer_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();

            // Frozen commercial snapshot (Section 41) - totals across all items at approval time
            $table->decimal('snapshot_total_selling_price', 14, 2)->nullable();
            $table->decimal('snapshot_total_vendor_cost', 14, 2)->nullable();
            $table->decimal('snapshot_total_tax', 14, 2)->nullable();
            $table->decimal('snapshot_total_other_cost', 14, 2)->nullable();
            $table->decimal('snapshot_final_landed_cost', 14, 2)->nullable();
            $table->decimal('snapshot_gross_profit', 14, 2)->nullable();
            $table->decimal('snapshot_gross_margin_percent', 8, 4)->nullable();

            $table->string('loading_source', 30)->nullable(); // DIRECT_CSP|DISTRIBUTOR
            $table->text('special_instructions')->nullable();
            $table->string('tenant_account', 150)->nullable();

            $table->boolean('low_margin_approval_required')->default(false);
            $table->boolean('management_approval_obtained')->default(false);

            $table->string('decision', 20)->nullable(); // APPROVE|RETURN_TO_SALES|REJECT|HOLD
            $table->text('remarks')->nullable(); // mandatory for Return/Reject/Hold (Rule 9)
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reviewer_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_approval_id')->constrained('reviewer_approvals')->cascadeOnDelete();
            $table->string('check_key', 60); // e.g. customer_information, work_order, sku, quantity, vendor_price_comparison...
            $table->string('label', 200);
            $table->boolean('is_checked')->default(false);
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();

            $table->unique(['reviewer_approval_id', 'check_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviewer_checklists');
        Schema::dropIfExists('reviewer_approvals');
    }
};
