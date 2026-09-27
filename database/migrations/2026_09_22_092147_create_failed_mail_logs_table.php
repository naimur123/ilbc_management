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
        Schema::create('failed_mail_logs', function (Blueprint $table) {
            $table->id();
            $table->string('template_slug')->nullable();
            $table->string('to_email')->nullable();
            $table->json('payload')->nullable();
            $table->json('attachments')->nullable();
            $table->text('error_message')->nullable();
            $table->text('error_trace')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_mail_logs');
    }
};
