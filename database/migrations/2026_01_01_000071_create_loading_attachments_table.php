<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loading_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loading_record_id')->constrained('loading_records')->cascadeOnDelete();
            $table->string('type', 30)->default('SCREENSHOT'); // SCREENSHOT|VENDOR_CONFIRMATION|CSP_SCREENSHOT|SUBSCRIPTION_CONFIRMATION
            $table->string('original_name', 255);
            $table->string('path', 255);
            $table->string('mime_type', 100)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loading_attachments');
    }
};
