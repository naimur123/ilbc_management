<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_acknowledgement_terms', function (Blueprint $table) {
            $table->id();
            $table->string('term_key', 100)->unique();
            $table->string('title', 150);
            $table->text('description');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        DB::table('customer_acknowledgement_terms')->insert([
            [
                'term_key' => 'license_details_quantity',
                'title' => 'License Details & Quantity',
                'description' => 'We confirm that the license details and quantities mentioned above are correct and approved by us.',
                'sort_order' => 1,
                'version' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'term_key' => 'commitment_billing',
                'title' => 'Commitment & Billing',
                'description' => 'We confirm that the commitment type, subscription term, and billing cycle mentioned above are correct and accepted by us.',
                'sort_order' => 2,
                'version' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'term_key' => 'subscription_period',
                'title' => 'Subscription Period',
                'description' => 'We confirm that the mentioned subscription start and end dates are correct and accepted by us.',
                'sort_order' => 3,
                'version' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'term_key' => 'authorized_use',
                'title' => 'Authorized Use',
                'description' => 'We agree to use the licensed products and services only for lawful, authorized, and legitimate purposes.',
                'sort_order' => 4,
                'version' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'term_key' => 'vendor_compliance',
                'title' => 'Vendor Compliance',
                'description' => 'We agree to comply with the applicable vendor licensing terms, policies, and acceptable use requirements.',
                'sort_order' => 5,
                'version' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'term_key' => 'license_usage',
                'title' => 'License Usage',
                'description' => 'We agree not to exceed the purchased license quantity or misuse, transfer, or sublicense the licenses unless permitted by the vendor.',
                'sort_order' => 6,
                'version' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'term_key' => 'payment_obligation',
                'title' => 'Payment Obligation',
                'description' => 'We acknowledge our responsibility for all applicable license, subscription, implementation, service, and other agreed charges.',
                'sort_order' => 7,
                'version' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'term_key' => 'information_accuracy',
                'title' => 'Information Accuracy',
                'description' => 'We confirm that all information provided for licensing, provisioning, and billing purposes is accurate and complete.',
                'sort_order' => 8,
                'version' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'term_key' => 'acceptance',
                'title' => 'Acceptance',
                'description' => 'By signing this form, we confirm that we have reviewed, understood, and accepted the above terms and conditions.',
                'sort_order' => 9,
                'version' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_acknowledgement_terms');
    }
};
