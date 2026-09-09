<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 40); // GENERAL|WORKFLOW|FINANCIAL|NOTIFICATION|INTEGRATION
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('cast_type', 20)->default('string'); // string|integer|decimal|boolean|json
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
