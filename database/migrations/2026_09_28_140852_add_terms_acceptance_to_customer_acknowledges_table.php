<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_acknowledges', function (Blueprint $table) {
            $table->boolean('terms_accepted')
                ->default(false);

            $table->unsignedInteger('terms_version')
                ->nullable();

            $table->json('terms_snapshot')
                ->nullable();

            $table->text('declaration_snapshot')
                ->nullable();

            $table->string('accepted_company_name', 255)
                ->nullable();

            $table->timestamp('terms_accepted_at')
                ->nullable();

            $table->string('accepted_ip_address', 45)
                ->nullable();

            $table->text('accepted_user_agent')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('customer_acknowledges', function (Blueprint $table) {
            $table->dropColumn([
                'terms_accepted',
                'terms_version',
                'terms_snapshot',
                'declaration_snapshot',
                'accepted_company_name',
                'terms_accepted_at',
                'accepted_ip_address',
                'accepted_user_agent',
            ]);
        });
    }
};