<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Change request (Sept 2026): Closure's "Collection Verification" checklist
 * (Section 10 of the redesigned Closure screen) needs somewhere to actually
 * record collection status/amount/date/reference/method — none of this
 * existed anywhere in the app before. It lives on billing_records (one row
 * per request, same place invoice_generated_at/invoice_sent_at/billing_done_at
 * already live) rather than a new table, since it is one snapshot per
 * request, entered once by the Closure team during final verification.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_records', function (Blueprint $table) {
            $table->string('collection_status', 20)->nullable()->after('billing_done_by'); // PENDING|PARTIAL|RECEIVED
            $table->decimal('collection_amount', 14, 2)->nullable()->after('collection_status');
            $table->date('collection_date')->nullable()->after('collection_amount');
            $table->string('payment_reference', 100)->nullable()->after('collection_date');
            $table->string('payment_method', 60)->nullable()->after('payment_reference');
            $table->decimal('outstanding_amount', 14, 2)->nullable()->after('payment_method');
            $table->foreignId('collection_recorded_by')->nullable()->after('outstanding_amount')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('collection_recorded_at')->nullable()->after('collection_recorded_by');
        });
    }

    public function down(): void
    {
        Schema::table('billing_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collection_recorded_by');
            $table->dropColumn([
                'collection_status', 'collection_amount', 'collection_date',
                'payment_reference', 'payment_method', 'outstanding_amount', 'collection_recorded_at',
            ]);
        });
    }
};
