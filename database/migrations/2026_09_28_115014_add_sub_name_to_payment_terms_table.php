<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_terms', function (Blueprint $table) {
            $table->string('sub_name', 50)
                ->nullable()
                ->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('payment_terms', function (Blueprint $table) {
            $table->dropColumn('sub_name');
        });
    }
};