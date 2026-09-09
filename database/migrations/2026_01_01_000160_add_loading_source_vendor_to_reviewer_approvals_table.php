<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Change request (Sept 2026): the Reviewer's "Loading Source" field must be
 * a dropdown of the actual Vendor list (which vendor will perform the
 * loading/installation), not the old free-text DIRECT_CSP/DISTRIBUTOR pair.
 * "Tenant / Account" is removed from the Reviewer Decision screen at the
 * same time — it is not known/required at approval time and is instead
 * captured per item, order-wise, during Loading (loading_records.tenant_account
 * already exists there and stays the single source of truth for it).
 *
 * The old `loading_source` string column is kept and repurposed to store a
 * frozen text snapshot of the chosen vendor's name at approval time (same
 * "cost snapshot" principle as Section 41 — if the vendor is renamed later,
 * an already-approved request keeps showing what was true when approved).
 * `tenant_account` on reviewer_approvals is left in place (nullable, simply
 * unused going forward) so existing rows/data are never destroyed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviewer_approvals', function (Blueprint $table) {
            $table->foreignId('loading_source_vendor_id')->nullable()->after('loading_source')
                ->constrained('vendors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reviewer_approvals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('loading_source_vendor_id');
        });
    }
};
