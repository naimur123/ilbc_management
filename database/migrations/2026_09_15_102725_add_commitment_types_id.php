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
        Schema::table('request_items', function (Blueprint $table) {
            if(!Schema::hasColumn('request_items', 'commitment_type_id')){
                $table->foreignId('commitment_type_id')->nullable()->constrained('commitment_types')->nullOnDelete();
            }
            if(!Schema::hasColumn('request_items', 'is_recurring')){
                $table->boolean('is_recurring')->default(0);
            }
            if(!Schema::hasColumn('request_items', 'recurring_months')){
                $table->integer('recurring_months')->nullable();
            }
        });

        Schema::table('sales_entries', function (Blueprint $table) {
            if(Schema::hasColumn('sales_entries', 'advance_percent')){
                $table->renameColumn('advance_percent', 'advance_amount');
            }
        });

        Schema::table('sales_entries', function (Blueprint $table) {
            if(Schema::hasColumn('sales_entries', 'advance_amount')){
                $table->bigInteger('advance_amount')->nullable()->default(0)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            if (Schema::hasColumn('request_items', 'commitment_type_id')) {
                $table->dropForeign(['commitment_type_id']);
                $table->dropColumn('commitment_type_id');
            }
    
            if (Schema::hasColumn('request_items', 'is_recurring')) {
                $table->dropColumn('is_recurring');
            }

            if (Schema::hasColumn('request_items', 'recurring_months')) {
                $table->dropColumn('recurring_months');
            }
        });
    
        Schema::table('sales_entries', function (Blueprint $table) {
            if (Schema::hasColumn('sales_entries', 'advance_amount')) {
                $table->unsignedTinyInteger('advance_amount')
                    ->default(0)
                    ->change();
    
                $table->renameColumn('advance_amount', 'advance_percent');
            }
        });
    }
};
