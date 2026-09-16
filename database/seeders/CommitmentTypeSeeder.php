<?php

namespace Database\Seeders;

use App\Models\CommitmentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommitmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Monthly', 'Annual'] as $name) {
            CommitmentType::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
