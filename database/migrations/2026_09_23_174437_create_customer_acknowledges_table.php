<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_acknowledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('request_item_id')->constrained('request_items');
            $table->foreignId('loading_record_id')->constrained('loading_records');
            $table->string('signatory_name')->nullable();
            $table->string('designation')->nullable();
            $table->date('acknowledgement_date')->nullable();
            $table->string('signature')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_acknowledges');
    }
};
