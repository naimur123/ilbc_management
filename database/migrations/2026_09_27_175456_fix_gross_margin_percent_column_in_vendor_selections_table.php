<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Widen gross_margin_percent columns from decimal(8,4) to decimal(10,4) so
 * they can store extreme negative margins (e.g. -59900.0000) without an
 * out-of-range error. decimal(10,4) allows values up to ±999999.9999 which
 * is sufficient for any real-world selling price vs cost scenario.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_selections', function (Blueprint $table) {
            $table->decimal('gross_margin_percent', 10, 4)->change();
        });

        Schema::table('reviewer_approvals', function (Blueprint $table) {
            $table->decimal('snapshot_gross_margin_percent', 10, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vendor_selections', function (Blueprint $table) {
            $table->decimal('gross_margin_percent', 8, 4)->change();
        });

        Schema::table('reviewer_approvals', function (Blueprint $table) {
            $table->decimal('snapshot_gross_margin_percent', 8, 4)->nullable()->change();
        });
    }
};
