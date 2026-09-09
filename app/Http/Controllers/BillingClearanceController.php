<?php

namespace App\Http\Controllers;

use App\Models\BillingClearance;
use App\Models\Request as WorkRequest;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BillingClearanceController extends Controller
{
    public function __construct(
        private WorkflowService $workflow,
        private NotificationService $notifications,
        private AuditLogService $auditLog,
    ) {
    }

    public function index(Request $request)
    {
        Gate::denyIf(! $request->user()->can('billing.clearance.view'));

        $requests = WorkRequest::with('customer', 'salesperson')
            ->when($request->filter === 'cleared', fn ($q) => $q->whereHas('billingClearances', fn ($q2) => $q2->where('decision', 'GREEN_SIGNAL')))
            ->when($request->filter === 'hold', fn ($q) => $q->whereIn('status', ['BILLING_ON_HOLD', 'BILLING_REJECTED']))
            ->when(!$request->filter, fn ($q) => $q->where('status', 'BILLING_CLEARANCE_PENDING'))
            ->latest()->paginate(20);

        return view('billing-clearance.index', compact('requests'));
    }

    public function show(WorkRequest $request)
    {
        Gate::denyIf(! auth()->user()->can('billing.clearance.view'));
        $request->load('customer', 'salesperson', 'items.product', 'items.sku', 'salesEntry');

        return view('billing-clearance.show', compact('request'));
    }

    public function decide(Request $httpRequest, WorkRequest $request)
    {
        $data = $httpRequest->validate([
            'decision' => 'required|in:GREEN_SIGNAL,HOLD,REJECT',
            'remarks' => 'nullable|string',
            'security_deposit_required' => 'nullable|boolean',
            'advance_payment_required' => 'nullable|boolean',
        ]);

        if ($data['decision'] !== 'GREEN_SIGNAL' && empty($data['remarks'])) {
            return back()->withErrors(['remarks' => 'Reason is mandatory for Hold/Reject.']);
        }

        Gate::denyIf(! $httpRequest->user()->can('billing.clearance.'.strtolower(str_replace('GREEN_SIGNAL', 'approve', $data['decision']))));

        $customer = $request->customer;

        $clearance = BillingClearance::create([
            'request_id' => $request->id,
            'customer_outstanding' => $customer->outstanding_balance,
            'has_previous_unpaid_invoice' => $customer->outstanding_balance > 0,
            'credit_limit_at_check' => $customer->credit_limit,
            'advance_payment_required' => $httpRequest->boolean('advance_payment_required'),
            'security_deposit_required' => $httpRequest->boolean('security_deposit_required'),
            'special_approval_required' => $customer->isOverCreditLimit(),
            'decision' => $data['decision'],
            'remarks' => $data['remarks'] ?? null,
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);

        // GREEN_SIGNAL moves the request straight into the Reviewer's queue
        // (Section 15: "Reviewer receives only Billing-cleared requests") —
        // BILLING_CLEARED is recorded on the clearance record itself above,
        // not as a separate request-level status, so it never gets stuck
        // between stages.
        $statusMap = ['GREEN_SIGNAL' => 'REVIEWER_PENDING', 'HOLD' => 'BILLING_ON_HOLD', 'REJECT' => 'BILLING_REJECTED'];
        $stage = $data['decision'] === 'GREEN_SIGNAL' ? 'REVIEWER' : 'BILLING_CLEARANCE';

        $this->workflow->transition($request, $statusMap[$data['decision']], $stage, $data['decision'], $data['remarks'] ?? null);

        if ($data['decision'] === 'GREEN_SIGNAL') {
            $this->notifications->notifyPermission('review.approve', 'Reviewer approval needed', "Request {$request->request_no} is ready for review.", $request->id, route('reviewer.show', $request));
        }

        $this->auditLog->record('Billing Clearance', $data['decision'], $request->id, null, $data['remarks'] ?? null);

        return redirect()->route('billing-clearance.index')->with('success', 'Decision recorded.');
    }
}
