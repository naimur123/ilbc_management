<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->string('name', 200);
            // M365, AZURE, DYNAMICS365, POWER_PLATFORM, WINDOWS_SERVER, SQL_SERVER, EMS, TEAMS, COPILOT, WINDOWS_365, OTHER
            $table->string('product_line', 30)->default('OTHER');
            // SMB, ENTERPRISE, FRONTLINE, EDUCATION, GOVERNMENT, ALL
            $table->string('segment', 30)->default('ALL');
            // CSP_NCE, CSP_LEGACY, EA, MOSP, PERPETUAL
            $table->string('licensing_program', 30)->default('CSP_NCE');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_line', 'segment']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
