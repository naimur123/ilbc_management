<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_acknowledges', function (Blueprint $table) {

            if (Schema::hasColumn(
                'customer_acknowledges',
                'signatory_name'
            )) {
                $table->dropColumn('signatory_name');
            }

            if (Schema::hasColumn(
                'customer_acknowledges',
                'designation'
            )) {
                $table->dropColumn('designation');
            }

            if (Schema::hasColumn(
                'customer_acknowledges',
                'acknowledgement_date'
            )) {
                $table->dropColumn('acknowledgement_date');
            }

            if (Schema::hasColumn(
                'customer_acknowledges',
                'signature'
            )) {
                $table->dropColumn('signature');
            }

        });
    }


    public function down(): void
    {
        Schema::table('customer_acknowledges', function (Blueprint $table) {

            $table->string(
                'signatory_name',
                150
            )->nullable();

            $table->string(
                'designation',
                150
            )->nullable();

            $table->date(
                'acknowledgement_date'
            )->nullable();

            $table->string(
                'signature',
                150
            )->nullable();

        });
    }
};