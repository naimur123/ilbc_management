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
        Schema::table('loading_records', function (Blueprint $table) {
            if(!Schema::hasColumn('loading_records', 'commitment_type_id')){
                $table->foreignId('commitment_type_id')->nullable()->constrained('commitment_types')->nullOnDelete();
            }
            if(!Schema::hasColumn('loading_records', 'billing_type_id')){
                $table->foreignId('billing_type_id')->nullable()->constrained('billing_types')->nullOnDelete();
            }
            if(!Schema::hasColumn('loading_records', 'is_recurring')){
                $table->boolean('is_recurring')->default(0);
            }
            if(!Schema::hasColumn('loading_records', 'recurring_months')){
                $table->integer('recurring_months')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loading_records', function (Blueprint $table) {
            if (Schema::hasColumn('loading_records', 'commitment_type_id')) {
                $table->dropForeign(['commitment_type_id']);
                $table->dropColumn('commitment_type_id');
            }

            if (Schema::hasColumn('loading_records', 'billing_type_id')) {
                $table->dropForeign(['billing_type_id']);
                $table->dropColumn('billing_type_id');
            }
    
            if (Schema::hasColumn('loading_records', 'is_recurring')) {
                $table->dropColumn('is_recurring');
            }

            if (Schema::hasColumn('loading_records', 'recurring_months')) {
                $table->dropColumn('recurring_months');
            }

        });
    }
};
