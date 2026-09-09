<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code', 30)->unique();
            $table->string('name', 200);
            $table->string('contact_person', 150)->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address', 255)->nullable();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('source', 100)->nullable(); // Source / Lead
            $table->string('customer_type', 50)->nullable(); // New / Existing / Government / Corporate
            $table->foreignId('payment_terms_id')->nullable()->constrained('payment_terms')->nullOnDelete();
            $table->decimal('credit_limit', 14, 2)->default(0);
            $table->decimal('outstanding_balance', 14, 2)->default(0);
            $table->string('status', 20)->default('ACTIVE'); // ACTIVE|INACTIVE|BLOCKED
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'mobile']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
