<?php

namespace Database\Seeders;

use App\Models\ApprovalRule;
use App\Models\BillingType;
use App\Models\Currency;
use App\Models\Department;
use App\Models\PaymentTerm;
use App\Models\SlaRule;
use App\Models\SubscriptionType;
use App\Models\Tax;
use App\Models\WorkflowDefinition;
use Illuminate\Database\Seeder;

/**
 * Ships enough master data for the system to be usable immediately after
 * install: lookups (Section 32), the default 8-stage workflow (Section 3)
 * as DATA (never hard-coded), stage-wise SLA rules (Section 34), and the
 * conditional approval rules (Section 17) with their default thresholds.
 */
class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCurrencies();
        $this->seedTaxes();
        $this->seedPaymentTerms();
        $this->seedBillingTypes();
        $this->seedSubscriptionTypes();
        $this->seedDepartments();
        $this->seedWorkflow();
        $this->seedSlaRules();
        $this->seedApprovalRules();
    }

    private function seedCurrencies(): void
    {
        Currency::firstOrCreate(['code' => 'BDT'], ['name' => 'Bangladeshi Taka', 'symbol' => '৳', 'exchange_rate_to_base' => 1, 'is_base' => true, 'is_active' => true]);
        Currency::firstOrCreate(['code' => 'USD'], ['name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_base' => 110, 'is_base' => false, 'is_active' => true]);
        Currency::firstOrCreate(['code' => 'EUR'], ['name' => 'Euro', 'symbol' => '€', 'exchange_rate_to_base' => 120, 'is_base' => false, 'is_active' => true]);
    }

    private function seedTaxes(): void
    {
        Tax::firstOrCreate(['name' => 'VAT 15%'], ['type' => 'VAT', 'rate_percent' => 15, 'is_active' => true]);
        Tax::firstOrCreate(['name' => 'AIT 5%'], ['type' => 'TAX', 'rate_percent' => 5, 'is_active' => true]);
        Tax::firstOrCreate(['name' => 'No Tax'], ['type' => 'NONE', 'rate_percent' => 0, 'is_active' => true]);
    }

    private function seedPaymentTerms(): void
    {
        PaymentTerm::firstOrCreate(['name' => 'Advance 100%'], ['advance_percent' => 100, 'credit_days' => 0, 'is_active' => true]);
        PaymentTerm::firstOrCreate(['name' => 'Net 30'], ['advance_percent' => 0, 'credit_days' => 30, 'is_active' => true]);
        PaymentTerm::firstOrCreate(['name' => 'Net 60'], ['advance_percent' => 0, 'credit_days' => 60, 'is_active' => true]);
        PaymentTerm::firstOrCreate(['name' => '50% Advance / 50% on Delivery'], ['advance_percent' => 50, 'credit_days' => 15, 'is_active' => true]);
    }

    private function seedBillingTypes(): void
    {
        foreach (['One-Time', 'Monthly', 'Quarterly', 'Annual', 'Usage-Based'] as $name) {
            BillingType::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }

    private function seedSubscriptionTypes(): void
    {
        foreach (['New', 'Renewal', 'Upgrade', 'Downgrade', 'Add-On'] as $name) {
            SubscriptionType::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }

    private function seedDepartments(): void
    {
        foreach ([['IT', 'DEP-IT'], ['Sales', 'DEP-SAL'], ['Finance', 'DEP-FIN'], ['Operations', 'DEP-OPS']] as [$name, $code]) {
            Department::firstOrCreate(['code' => $code], ['name' => $name, 'is_active' => true]);
        }
    }

    private function seedWorkflow(): void
    {
        $definition = WorkflowDefinition::firstOrCreate(['name' => 'Default ILBC Workflow'], ['is_active' => true]);

        $steps = [
            ['SALES', 'Sales Entry', 'sales.create', 4],
            ['BILLING_CLEARANCE', 'Billing Clearance', 'billing.clearance.approve', 4],
            ['REVIEWER', 'Reviewer Approval', 'review.approve', 4],
            ['LOADING', 'Loading / Installation', 'loading.complete', 8],
            ['AUDIT', 'Audit', 'audit.approve', 4],
            ['BILLING', 'Billing / Invoice', 'invoice.mark_done', 8],
            ['CLOSURE', 'Closure', 'closure.close', 4],
        ];

        foreach ($steps as $i => [$key, $name, $permission, $slaHours]) {
            $definition->steps()->firstOrCreate(
                ['step_key' => $key],
                ['name' => $name, 'step_order' => $i + 1, 'required_permission' => $permission, 'sla_hours' => $slaHours]
            );
        }
    }

    private function seedSlaRules(): void
    {
        $rules = [
            ['BILLING_CLEARANCE', 'Billing Clearance', 4],
            ['REVIEWER', 'Reviewer Approval', 4],
            ['LOADING', 'Loading', 8],
            ['AUDIT', 'Audit', 4],
            ['BILLING', 'Billing', 8],
            ['CLOSURE', 'Closure', 4],
        ];

        foreach ($rules as [$key, $label, $hours]) {
            SlaRule::firstOrCreate(['stage_key' => $key], ['label' => $label, 'sla_hours' => $hours, 'due_soon_threshold_hours' => max(1, (int) round($hours * 0.25))]);
        }
    }

    private function seedApprovalRules(): void
    {
        // required_role is a non-nullable column (Section 17: "Management
        // Approval Required" / "Finance Approval Required" / etc.) — it
        // names which role's approval the rule escalates to. There is no
        // separate "Department Head" role in this app's actual role set
        // (Section 4), so High Value Sales Approval escalates to Management
        // the same as Low Margin and Vendor-not-lowest-cost.
        $rules = [
            ['Low Margin Approval', 'MARGIN_PERCENT', '<', config('ilbc.minimum_margin_percent', 10), 'Management'],
            ['High Value Sales Approval', 'SALES_AMOUNT', '>', config('ilbc.department_head_approval_amount', 500000), 'Management'],
            ['Outstanding Exceeds Credit Limit', 'OUTSTANDING_VS_CREDIT_LIMIT', '>', 0, 'Finance'],
            ['Vendor Not Lowest Cost', 'VENDOR_NOT_LOWEST', '==', 1, 'Management'],
        ];

        foreach ($rules as [$name, $field, $operator, $threshold, $requiredRole]) {
            ApprovalRule::firstOrCreate(
                ['name' => $name],
                ['condition_field' => $field, 'operator' => $operator, 'threshold_value' => $threshold, 'required_role' => $requiredRole, 'is_active' => true]
            );
        }
    }
}
