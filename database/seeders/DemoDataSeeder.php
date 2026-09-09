<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Department;
use App\Models\PaymentTerm;
use App\Models\ProductSku;
use App\Models\Request as WorkRequest;
use App\Models\RequestItem;
use App\Models\Salesperson;
use App\Models\User;
use App\Models\Vendor;
use App\Services\VendorPriceService;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

/**
 * Optional demo data: sample vendors/customers/salespersons/users covering
 * every role, plus one Draft request already carrying multiple items and
 * vendor prices — so a fresh install has something on screen to click
 * through instead of empty grids everywhere. Never seeds fake financial
 * approvals/closures (that would defeat the point of walking the real
 * workflow), and is skip-safe to re-run.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::first();

        $users = [
            ['Admin User', 'admin@ilbc.local', ['Super Admin']],
            ['Sales User', 'sales@ilbc.local', ['Sales']],
            ['Billing User', 'billing@ilbc.local', ['Billing']],
            ['Reviewer User', 'reviewer@ilbc.local', ['Reviewer']],
            ['Loader User', 'loader@ilbc.local', ['Loader']],
            ['Auditor User', 'auditor@ilbc.local', ['Auditor']],
            ['Closer User', 'closer@ilbc.local', ['Closer']],
            ['Management User', 'management@ilbc.local', ['Management']],
            ['Finance User', 'finance@ilbc.local', ['Finance']],
        ];

        foreach ($users as [$name, $email, $roles]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('password'), 'is_active' => true, 'department_id' => $department?->id]
            );
            $user->syncRoles($roles);
        }

        $salespersonUser = User::where('email', 'sales@ilbc.local')->first();
        $salesperson = Salesperson::firstOrCreate(
            ['code' => 'SP-0001'],
            ['user_id' => $salespersonUser?->id, 'name' => 'Demo Salesperson', 'department_id' => $department?->id, 'is_active' => true]
        );

        $customer = Customer::firstOrCreate(
            ['customer_code' => 'CUS-0001'],
            [
                'name' => 'Demo Customer Ltd.', 'contact_person' => 'Md. Karim', 'mobile' => '01700000000',
                'email' => 'accounts@democustomer.example', 'address' => 'Dhaka, Bangladesh',
                'department_id' => $department?->id, 'customer_type' => 'Corporate',
                'payment_terms_id' => PaymentTerm::first()?->id, 'credit_limit' => 500000, 'outstanding_balance' => 0,
                'status' => 'ACTIVE',
            ]
        );

        $vendors = [
            ['VEN-0001', 'Smart Technologies', 'DISTRIBUTOR', 5200],
            ['VEN-0002', 'Crayon Bangladesh', 'CSP', 5000],
            ['VEN-0003', 'Trident Solutions', 'DISTRIBUTOR', 5250],
        ];

        $sku = ProductSku::where('sku_code', 'M365-BS')->first();

        foreach ($vendors as [$code, $name, $type, $unitCost]) {
            $vendor = Vendor::firstOrCreate(
                ['vendor_code' => $code],
                ['name' => $name, 'vendor_type' => $type, 'status' => 'ACTIVE', 'lead_time_days' => 3]
            );
            $vendor->provisioningAccount()->firstOrCreate([], ['provider' => 'manual', 'is_enabled' => false]);

            if ($sku && ! $vendor->productPrices()->where('product_sku_id', $sku->id)->exists()) {
                app(VendorPriceService::class)->setPrice($vendor, $sku, [
                    'unit_purchase_price' => $unitCost,
                    'currency_id' => \App\Models\Currency::where('code', 'BDT')->value('id'),
                    'vat_percent' => 15,
                    'tax_percent' => 5,
                    'effective_from' => now()->subDays(30)->toDateString(),
                ]);
            }
        }

        if ($sku && ! WorkRequest::where('request_no', 'like', 'REQ-%')->where('customer_id', $customer->id)->exists()) {
            $request = WorkRequest::create([
                'request_no' => WorkRequest::generateRequestNo(),
                'customer_id' => $customer->id,
                'salesperson_id' => $salesperson->id,
                'department_id' => $department?->id,
                'work_order_no' => 'WO-DEMO-0001',
                'status' => 'DRAFT',
                'current_stage' => 'SALES',
                'created_by' => $salespersonUser?->id ?? User::first()->id,
                'current_stage_started_at' => now(),
            ]);

            $request->salesEntry()->create([
                'contact_person' => $customer->contact_person,
                'mobile' => $customer->mobile,
                'email' => $customer->email,
                'customer_type' => $customer->customer_type,
                'payment_terms_id' => $customer->payment_terms_id,
                'advance_percent' => 0,
                'credit_days' => 30,
                'billing_cycle' => 'Monthly',
                'remarks' => 'Demo request seeded for a fresh install — walk it through Billing Clearance > Reviewer > Loading > Audit > Billing > Closure.',
            ]);

            RequestItem::create([
                'request_id' => $request->id,
                'product_category_id' => $sku->product->product_category_id,
                'product_id' => $sku->product_id,
                'product_sku_id' => $sku->id,
                'quantity' => 10,
                'unit_selling_price' => 6000,
                'total_selling_price' => 60000,
            ]);

            app(WorkflowService::class)->ensureInstance($request);
        }
    }
}
