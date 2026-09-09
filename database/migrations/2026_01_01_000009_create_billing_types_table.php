<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60); // Monthly, Annual, One-Time, Quarterly
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subscription_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60); // New, Renewal, Upgrade, Add-on, Migration
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_types');
        Schema::dropIfExists('billing_types');
    }
};
