<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configurable conditional approval rules (Section 17), evaluated by
 * ApprovalService against a request/item and, when triggered, creating an
 * approval_requests row that blocks Reviewer approval until resolved.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('condition_field', 40); // MARGIN_PERCENT|SALES_AMOUNT|OUTSTANDING_VS_CREDIT_LIMIT|VENDOR_NOT_LOWEST
            $table->string('operator', 10); // <|<=|>|>=|==|!=
            $table->decimal('threshold_value', 14, 4)->nullable();
            $table->string('required_role', 60); // e.g. Management, Finance, Department Head
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('approval_rule_id')->constrained('approval_rules');
            $table->string('status', 20)->default('PENDING'); // PENDING|APPROVED|REJECTED
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_rules');
    }
};
