<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_code', 30)->unique();
            $table->string('name', 200);
            $table->string('vendor_type', 30); // DISTRIBUTOR|CSP|DIRECT_VENDOR
            $table->string('contact_person', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address', 255)->nullable();
            $table->foreignId('payment_terms_id')->nullable()->constrained('payment_terms')->nullOnDelete();
            $table->decimal('credit_limit', 14, 2)->default(0);
            $table->string('tax_vat_number', 60)->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->unsignedSmallInteger('lead_time_days')->default(0);
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE|INACTIVE|SUSPENDED
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vendor_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('designation', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_contacts');
        Schema::dropIfExists('vendors');
    }
};
