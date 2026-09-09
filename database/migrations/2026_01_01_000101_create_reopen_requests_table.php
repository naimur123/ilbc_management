<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reopen_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->text('reason');
            $table->string('target_stage', 30); // which stage to reopen back to
            $table->foreignId('requested_by')->constrained('users');
            $table->timestamp('requested_at');
            $table->string('status', 20)->default('PENDING'); // PENDING|APPROVED|REJECTED
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('decision_remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reopen_requests');
    }
};
