<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Single unified audit trail table (Section 30) — also powers the
 * Dashboard's "Recent Activity" feed. Never editable/deletable by
 * standard users (no update/delete route is ever registered for it).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role_name', 60)->nullable();
            $table->foreignId('request_id')->nullable()->constrained('requests')->nullOnDelete();
            $table->string('module', 60); // Sales, Billing, Reviewer, Vendor, Loading, Audit, Invoice, Closure, Settings...
            $table->string('action', 100);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['request_id', 'created_at']);
            $table->index(['module', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
