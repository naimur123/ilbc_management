<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-item vendor selection (Section 13/42): each request_item gets its
 * own vendor pick, with the full cost-comparison breakdown frozen at
 * selection time via vendor_product_price_id + the computed columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_item_id')->constrained('request_items')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->foreignId('vendor_product_price_id')->nullable()->constrained('vendor_product_prices')->nullOnDelete();

            $table->decimal('unit_cost', 14, 2);
            $table->decimal('base_cost', 14, 2);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('handling_cost', 14, 2)->default(0);
            $table->decimal('delivery_cost', 14, 2)->default(0);
            $table->decimal('other_cost', 14, 2)->default(0);
            $table->decimal('final_landed_cost', 14, 2);
            $table->decimal('gross_profit', 14, 2);
            $table->decimal('gross_margin_percent', 8, 4);

            $table->boolean('is_lowest_cost_vendor')->default(false);
            $table->text('override_reason')->nullable(); // mandatory if not lowest cost (Rule 8) or manual price override (Rule 7)
            $table->foreignId('selected_by')->constrained('users');
            $table->timestamps();

            $table->unique('request_item_id'); // one active vendor selection per item
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_selections');
    }
};
