<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the (currently dormant) vendor provisioning automation tables.
 * Creating these tables does not change any existing behavior — every
 * vendor defaults to provider = 'manual' and is_enabled = false, so
 * Loading stays 100% manual until a vendor is deliberately switched on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_provisioning_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('provider', 20)->default('manual'); // manual|partner_center|crayon
            $table->string('tenant_id', 100)->nullable();      // vendor/distributor account or tenant reference
            $table->boolean('is_enabled')->default(false);
            $table->boolean('sandbox_mode')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['vendor_id', 'provider']);
        });

        Schema::create('vendor_api_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_provisioning_account_id')
                ->constrained('vendor_provisioning_accounts')->cascadeOnDelete();
            $table->string('credential_type', 30); // oauth_client|api_key
            $table->text('encrypted_payload');      // Crypt::encryptString(json_encode([...]))
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // MySQL identifiers cap at 64 chars — the auto-generated name for
            // this compound index (table + both column names + "_index")
            // comes out to 75 chars, so it needs an explicit short name.
            $table->index(['vendor_provisioning_account_id', 'credential_type'], 'vendor_api_credentials_account_cred_type_index');
        });

        Schema::create('provisioning_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_item_id')->constrained('request_items')->cascadeOnDelete();
            $table->foreignId('loading_record_id')->nullable()->constrained('loading_records')->nullOnDelete();
            $table->string('provider', 20);           // manual|partner_center|crayon
            $table->string('action', 20);             // CREATE|SUSPEND|RESUME|DELETE
            $table->string('status', 20)->default('PENDING'); // PENDING|RUNNING|SUCCEEDED|FAILED
            $table->string('external_reference', 120)->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'provider']);
            $table->index('external_reference');
        });

        Schema::create('provisioning_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provisioning_job_id')->constrained('provisioning_jobs')->cascadeOnDelete();
            $table->json('request_payload')->nullable();  // secrets redacted before storage
            $table->json('response_payload')->nullable(); // secrets redacted before storage
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('provisioning_job_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provisioning_logs');
        Schema::dropIfExists('provisioning_jobs');
        Schema::dropIfExists('vendor_api_credentials');
        Schema::dropIfExists('vendor_provisioning_accounts');
    }
};
