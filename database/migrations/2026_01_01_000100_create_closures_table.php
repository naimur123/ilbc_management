<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('closures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->unique()->constrained('requests')->cascadeOnDelete();
            $table->foreignId('closed_by')->constrained('users');
            $table->date('closure_date');
            $table->time('closure_time');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('closures');
    }
};
