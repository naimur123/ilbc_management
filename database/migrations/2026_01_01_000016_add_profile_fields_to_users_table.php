<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_code', 30)->nullable()->unique()->after('id');
            $table->string('phone', 30)->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('phone');
            $table->foreignId('department_id')->nullable()->after('is_active')
                ->constrained('departments')->nullOnDelete();
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn(['employee_code', 'phone', 'is_active', 'last_login_at']);
            $table->dropSoftDeletes();
        });
    }
};
