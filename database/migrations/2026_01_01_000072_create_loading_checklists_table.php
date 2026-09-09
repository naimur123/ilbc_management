<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loading_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loading_record_id')->constrained('loading_records')->cascadeOnDelete();
            $table->string('check_key', 60);
            $table->string('label', 200);
            $table->boolean('is_checked')->default(false);
            $table->timestamps();

            $table->unique(['loading_record_id', 'check_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loading_checklists');
    }
};
