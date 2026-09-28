<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payment_terms')->update([
           'is_active' => 0
        ]);

        DB::table('payment_terms')->insert([
            [
                'name' => 'Advance',
                'sub_name' => 'advance',
                'advance_percent' => 0,
                'credit_days' => 0,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'No Advance',
                'sub_name' => 'no_advance',
                'advance_percent' => 0,
                'credit_days' => 0,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        //
    }
};