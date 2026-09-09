<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->string('invoice_no', 40)->unique();
            $table->date('invoice_date');
            $table->string('billing_month', 10); // MMM-YYYY per master prompt's month format
            $table->date('billing_period_from')->nullable();
            $table->date('billing_period_to')->nullable();
            $table->decimal('invoice_amount', 14, 2);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('other_charges', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2);
            $table->date('due_date')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->string('status', 20)->default('GENERATED'); // GENERATED|SENT|PAID
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('request_item_id')->constrained('request_items');
            $table->string('description', 255);
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('line_total', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
