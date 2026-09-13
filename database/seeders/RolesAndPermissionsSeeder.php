<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Section 4/5: every permission string from the master prompt, and the
 * default operational roles. Super Admin/Administrator bypass every
 * permission check via Gate::before (AuthServiceProvider) so they are not
 * given explicit permissions here — everyone else gets exactly what the
 * spec lists for their role, and an Admin can create unlimited custom
 * roles from Administration > User & Role Management without touching code.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public const PERMISSIONS = [
        'dashboard.view',

        'request.view', 'request.view_own', 'request.view_all', 'request.create',
        'request.edit', 'request.delete', 'request.cancel', 'request.reopen',

        'sales.create', 'sales.edit', 'sales.view_cost', 'sales.view_margin',

        'billing.clearance.view', 'billing.clearance.approve', 'billing.clearance.reject', 'billing.clearance.hold',

        'review.view', 'review.approve', 'review.reject', 'review.return', 'review.hold',

        'vendor.view', 'vendor.create', 'vendor.edit', 'vendor.delete', 'vendor.price_manage', 'vendor.select', 'vendor.api_manage',

        'loading.view', 'loading.process', 'loading.complete', 'loading.edit',

        // SLA Management (change request, Sept 2026): view the SLA queues
        // and reports; manage = complete/create SLA records day-to-day;
        // waive = management-approved exception; configure = the SLA
        // Configuration rules screen and the "required before Audit" toggle.
        'sla.view', 'sla.manage', 'sla.waive', 'sla.configure', 'sla.report',

        'audit.view', 'audit.approve', 'audit.return', 'audit.hold',

        'invoice.view', 'invoice.create', 'invoice.edit', 'invoice.mark_sent', 'invoice.mark_done',

        'closure.view', 'closure.close', 'closure.reopen',

        'report.view', 'report.export',

        'user.manage', 'role.manage', 'permission.manage',

        'workflow.manage', 'settings.manage',

        'audit_log.view',
    ];

    /**
     * Role => permissions. Super Admin/Administrator are intentionally
     * omitted (they bypass via Gate::before) but still created here so
     * they exist to assign to users immediately after install.
     */
    public const ROLE_PERMISSIONS = [
        'Super Admin' => [],
        'Administrator' => [],
        'Management' => [
            'dashboard.view', 'request.view', 'request.view_all',
            'sales.view_cost', 'sales.view_margin',
            'review.view', 'vendor.view', 'loading.view', 'audit.view', 'invoice.view', 'closure.view',
            'sla.view', 'sla.waive', 'sla.report',
            'report.view', 'report.export', 'audit_log.view',
        ],
        'Sales' => [
            'dashboard.view', 'request.view', 'request.view_own', 'request.create', 'request.edit',
            'request.cancel', 'sales.create', 'sales.edit', 'report.view',
        ],
        'Billing' => [
            'dashboard.view', 'request.view', 'request.view_all',
            'billing.clearance.view', 'billing.clearance.approve', 'billing.clearance.reject', 'billing.clearance.hold',
            'invoice.view', 'invoice.create', 'invoice.edit', 'invoice.mark_sent', 'invoice.mark_done', 'report.view',
        ],
        'Reviewer' => [
            'dashboard.view', 'request.view', 'request.view_all',
            'review.view', 'review.approve', 'review.reject', 'review.return', 'review.hold',
            'sales.view_cost', 'sales.view_margin', 'vendor.view', 'vendor.select', 'report.view',
        ],
        'Loader' => [
            'dashboard.view', 'request.view', 'request.view_all',
            'loading.view', 'loading.process', 'loading.complete', 'loading.edit',
            'sla.view', 'sla.manage', 'report.view',
        ],
        'Auditor' => [
            'dashboard.view', 'request.view', 'request.view_all',
            'audit.view', 'audit.approve', 'audit.return', 'audit.hold', 'sla.view', 'report.view',
        ],
        'Closer' => [
            'dashboard.view', 'request.view', 'request.view_all',
            'closure.view', 'closure.close', 'closure.reopen', 'report.view',
        ],
        'Finance' => [
            'dashboard.view', 'request.view', 'request.view_all',
            'sales.view_cost', 'sales.view_margin', 'billing.clearance.view',
            'invoice.view', 'report.view', 'report.export', 'audit_log.view',
        ],
        'View Only' => [
            'dashboard.view', 'request.view', 'request.view_all', 'sla.view', 'report.view',
        ],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            foreach (self::PERMISSIONS as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            }

            foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
                $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
                $role->syncPermissions($permissions);
            }
        });
    }
}
