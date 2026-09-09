<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per product/SKU line on a request (Section 10). Everything
 * downstream — vendor selection, loading, audit variance — is keyed to
 * request_item_id so each item can use a different vendor (Section 42).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('product_category_id')->constrained('product_categories');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('product_sku_id')->constrained('product_skus');
            $table->string('description', 255)->nullable();
            $table->decimal('quantity', 12, 2)->default(1);
            $table->foreignId('billing_type_id')->nullable()->constrained('billing_types')->nullOnDelete();
            $table->foreignId('subscription_type_id')->nullable()->constrained('subscription_types')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('unit_selling_price', 14, 2);
            $table->decimal('total_selling_price', 14, 2); // quantity * unit_selling_price, kept as a stored column
            $table->string('status', 30)->default('PENDING'); // mirrors parent request's stage for per-item tracking
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_items');
    }
};
