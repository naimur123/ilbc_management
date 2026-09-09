<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One loading/installation transaction per request_item (Section 18-20).
 * Read-only commercial fields are NOT duplicated here — the Loading screen
 * reads them live from request_items/vendor_selections/reviewer_approvals;
 * this table only stores what the Loader actually enters.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loading_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_item_id')->unique()->constrained('request_items')->cascadeOnDelete();

            $table->date('loading_date')->nullable();
            $table->time('loading_time')->nullable();
            $table->decimal('actual_loaded_quantity', 12, 2)->nullable();
            $table->string('subscription_id', 100)->nullable();
            $table->string('license_id', 100)->nullable();
            $table->string('tenant_account', 150)->nullable();
            $table->date('activation_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('vendor_reference', 100)->nullable();
            $table->string('distributor_reference', 100)->nullable();
            $table->string('po_reference', 100)->nullable();
            $table->text('technical_notes')->nullable();

            $table->string('status', 20)->default('PENDING'); // PENDING|IN_PROGRESS|COMPLETED
            $table->foreignId('loaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loading_records');
    }
};
