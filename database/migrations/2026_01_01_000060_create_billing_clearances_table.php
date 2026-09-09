<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Billing Pre-Check (Section 14). One row per decision so a Hold->Cleared
 * history is preserved; RequestPolicy/WorkflowService always reads the
 * latest row for the current gate.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_clearances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->decimal('customer_outstanding', 14, 2)->default(0);
            $table->boolean('has_previous_unpaid_invoice')->default(false);
            $table->decimal('credit_limit_at_check', 14, 2)->default(0);
            $table->boolean('advance_payment_required')->default(false);
            $table->boolean('security_deposit_required')->default(false);
            $table->boolean('special_approval_required')->default(false);
            $table->string('decision', 20); // GREEN_SIGNAL|HOLD|REJECT
            $table->text('remarks')->nullable(); // mandatory when HOLD/REJECT (Rule 9)
            $table->foreignId('decided_by')->constrained('users');
            $table->timestamp('decided_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_clearances');
    }
};
