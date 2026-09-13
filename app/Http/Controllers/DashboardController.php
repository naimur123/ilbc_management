<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Request as WorkRequest;
use App\Models\RequestSla;
use App\Services\SLAService;
use App\Services\SlaMonitoringService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(SLAService $sla, SlaMonitoringService $slaMonitoring)
    {
        $user = Auth::user();
        $now = Carbon::now();

        $base = WorkRequest::query();

        $kpi = [
            'total' => (clone $base)->count(),
            'this_month' => (clone $base)->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count(),
            'pending' => (clone $base)->whereNotIn('status', ['CLOSED', 'CANCELLED'])->count(),
            'billing_clearance_pending' => (clone $base)->where('status', 'BILLING_CLEARANCE_PENDING')->count(),
            'reviewer_pending' => (clone $base)->where('status', 'REVIEWER_PENDING')->count(),
            'loading_pending' => (clone $base)->whereIn('status', ['LOADING_PENDING', 'LOADING_IN_PROGRESS'])->count(),
            'audit_pending' => (clone $base)->where('status', 'AUDIT_PENDING')->count(),
            'billing_pending' => (clone $base)->whereIn('status', ['BILLING_PENDING', 'INVOICE_GENERATED', 'INVOICE_SENT'])->count(),
            'completed' => (clone $base)->where('status', 'BILLING_DONE')->count(),
            'closed' => (clone $base)->where('status', 'CLOSED')->count(),
            'rejected' => (clone $base)->whereIn('status', ['BILLING_REJECTED', 'REVIEWER_RETURNED'])->count(),
            'on_hold' => (clone $base)->where('status', 'BILLING_HOLD')->count(),
        ];

        $requests = (clone $base)->whereNotIn('status', ['CLOSED', 'CANCELLED', 'DRAFT'])->get();
        $kpi['sla_overdue'] = $requests->filter(fn ($r) => $sla->statusFor($r) === 'OVERDUE')->count();

        $statusByStage = (clone $base)->whereNotIn('status', ['CLOSED', 'CANCELLED', 'DRAFT'])
            ->selectRaw('current_stage, count(*) as total')->groupBy('current_stage')->pluck('total', 'current_stage');

        $slaBuckets = ['ON_TIME' => 0, 'DUE_SOON' => 0, 'OVERDUE' => 0];
        foreach ($requests as $r) {
            $slaBuckets[$sla->statusFor($r)] = ($slaBuckets[$sla->statusFor($r)] ?? 0) + 1;
        }

        $financial = [
            'total_sales' => (float) \App\Models\RequestItem::sum('total_selling_price'),
            'vendor_purchase_value' => (float) \App\Models\VendorSelection::sum('final_landed_cost'),
            'invoice_value' => (float) \App\Models\Invoice::sum('total_amount'),
            'billing_pending_value' => (float) \App\Models\RequestItem::whereHas('request', fn ($q) => $q->whereIn('status', ['BILLING_PENDING', 'AUDIT_APPROVED']))->sum('total_selling_price'),
        ];
        $financial['gross_profit'] = $financial['total_sales'] - $financial['vendor_purchase_value'];
        $financial['gross_margin_percent'] = $financial['total_sales'] > 0 ? round($financial['gross_profit'] / $financial['total_sales'] * 100, 2) : 0;

        $pendingTasks = WorkRequest::with(['customer'])
            ->whereNotIn('status', ['CLOSED', 'CANCELLED', 'DRAFT'])
            ->when(! $user->hasRole(['Super Admin', 'Administrator', 'Management']), function ($q) use ($user) {
                $q->where(function ($q2) use ($user) {
                    $q2->where('created_by', $user->id)
                        ->orWhereHas('salesperson', fn ($q3) => $q3->where('user_id', $user->id));
                });
            })
            ->latest('current_stage_started_at')->take(8)->get();

        $recentActivity = AuditLog::with('user', 'request')->latest('created_at')->take(8)->get();

        // SLA Management (change request, Sept 2026): dashboard KPI cards +
        // "My SLA Tasks" — this is the NEW request_slas overlay module,
        // kept deliberately separate from the SLAService/$sla stage-aging
        // KPIs above (sla_overdue, slaBuckets), which are untouched.
        if (\Illuminate\Support\Facades\Schema::hasTable('request_slas')) {
            $slaMonitoring->refreshAll();

            $slaKpi = [
                'active' => RequestSla::whereIn('status', ['ACTIVE', 'DUE_SOON'])->count(),
                'due_today' => RequestSla::whereNotIn('status', RequestSla::FINAL_STATUSES)->whereDate('target_at', $now->toDateString())->count(),
                'due_within_4h' => RequestSla::whereNotIn('status', RequestSla::FINAL_STATUSES)->whereBetween('target_at', [$now, $now->copy()->addHours(4)])->count(),
                'overdue' => RequestSla::where('status', 'OVERDUE')->count(),
                'completed_within' => RequestSla::where('status', 'COMPLETED_WITHIN_SLA')->count(),
                'breached' => RequestSla::where('status', 'COMPLETED_LATE')->count(),
            ];
            $completedTotal = $slaKpi['completed_within'] + $slaKpi['breached'];
            $slaKpi['compliance_percent'] = $completedTotal > 0 ? round($slaKpi['completed_within'] / $completedTotal * 100, 1) : 100;

            $mySlaTasks = RequestSla::with('request.customer', 'responsibleUser')
                ->whereNotIn('status', RequestSla::FINAL_STATUSES)
                ->when(! $user->hasRole(['Super Admin', 'Administrator', 'Management']), fn ($q) => $q->where('responsible_user_id', $user->id))
                ->orderBy('target_at')->take(8)->get();
        } else {
            // sla:process migration not yet run on this install — dashboard
            // still renders normally with the SLA section simply empty.
            $slaKpi = ['active' => 0, 'due_today' => 0, 'due_within_4h' => 0, 'overdue' => 0, 'completed_within' => 0, 'breached' => 0, 'compliance_percent' => 100];
            $mySlaTasks = collect();
        }

        $monthlyFlow = [
            'Sales' => WorkRequest::count(),
            'Billing Cleared' => WorkRequest::where('status', '!=', 'BILLING_CLEARANCE_PENDING')->whereNotIn('status', ['DRAFT'])->count(),
            'Reviewer Approved' => WorkRequest::whereNotIn('status', ['DRAFT', 'SUBMITTED', 'BILLING_CLEARANCE_PENDING', 'BILLING_REJECTED', 'BILLING_HOLD', 'REVIEWER_PENDING', 'REVIEWER_RETURNED'])->count(),
            'Loading Completed' => WorkRequest::whereNotIn('status', ['DRAFT', 'SUBMITTED', 'BILLING_CLEARANCE_PENDING', 'BILLING_REJECTED', 'BILLING_HOLD', 'REVIEWER_PENDING', 'REVIEWER_RETURNED', 'LOADING_PENDING', 'LOADING_IN_PROGRESS'])->count(),
            'Audit Approved' => WorkRequest::whereIn('status', ['AUDIT_APPROVED', 'BILLING_PENDING', 'INVOICE_GENERATED', 'INVOICE_SENT', 'BILLING_DONE', 'READY_FOR_CLOSURE', 'CLOSED'])->count(),
            'Invoice Generated' => WorkRequest::whereIn('status', ['INVOICE_GENERATED', 'INVOICE_SENT', 'BILLING_DONE', 'READY_FOR_CLOSURE', 'CLOSED'])->count(),
            'Closed' => WorkRequest::where('status', 'CLOSED')->count(),
        ];

        return view('dashboard.index', compact('kpi', 'statusByStage', 'slaBuckets', 'financial', 'pendingTasks', 'recentActivity', 'monthlyFlow', 'sla', 'slaKpi', 'mySlaTasks'));
    }
}
