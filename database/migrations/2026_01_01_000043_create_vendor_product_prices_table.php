<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Current + historical vendor cost per SKU (Section 12). A new row is
 * inserted whenever a price changes; the previous row's effective_to is
 * closed off and copied into vendor_price_history — rows here are never
 * overwritten (see VendorPriceService::updatePrice()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_sku_id')->constrained('product_skus')->cascadeOnDelete();
            $table->decimal('unit_purchase_price', 14, 2);
            $table->string('cost_unit', 30)->nullable(); // mirrors product_skus.consumption_unit
            $table->foreignId('currency_id')->constrained('currencies');
            $table->decimal('vat_percent', 6, 3)->default(0);
            $table->decimal('tax_percent', 6, 3)->default(0);
            $table->decimal('other_cost', 14, 2)->default(0);
            $table->decimal('handling_cost', 14, 2)->default(0);
            $table->decimal('delivery_cost', 14, 2)->default(0);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->unsignedInteger('minimum_quantity')->default(1);
            // LIST, PARTNER_DISCOUNTED, PROMO, CUSTOM_QUOTE
            $table->string('price_type', 30)->default('LIST');
            $table->boolean('is_current')->default(true);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_sku_id', 'vendor_id', 'is_current']);
        });

        Schema::create('vendor_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_product_price_id')->constrained('vendor_product_prices')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->foreignId('product_sku_id')->constrained('product_skus');
            $table->decimal('old_unit_purchase_price', 14, 2)->nullable();
            $table->decimal('new_unit_purchase_price', 14, 2);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_price_history');
        Schema::dropIfExists('vendor_product_prices');
    }
};
