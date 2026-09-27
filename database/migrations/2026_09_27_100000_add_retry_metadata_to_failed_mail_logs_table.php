<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('failed_mail_logs', function (Blueprint $table) {
            $table->string('status')->default('failed');
            $table->unsignedInteger('attempt_count')->default(0);
            $table->string('transport')->default('smtp');
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('failed_mail_logs', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'attempt_count',
                'transport',
                'last_attempt_at',
                'sent_at',
            ]);
        });
    }
};