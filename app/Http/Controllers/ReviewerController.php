<?php

namespace App\Http\Controllers;

use App\Models\Request as WorkRequest;
use App\Models\RequestItem;
use App\Models\ReviewerApproval;
use App\Models\Vendor;
use App\Models\VendorProductPrice;
use App\Models\VendorSelection;
use App\Services\ApprovalService;
use App\Services\AuditLogService;
use App\Services\CostCalculationService;
use App\Services\MarginCalculationService;
use App\Services\NotificationService;
use App\Services\VendorPriceService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReviewerController extends Controller
{
    /**
     * Section 16's mandatory checklist items — data-driven per approval
     * instance so the questions can evolve without touching this class,
     * but seeded here with a sane default set the first time a Reviewer
     * opens a request (no separate seeder run required for a fresh install).
     */
    public const CHECKLIST_ITEMS = [
        'customer_information' => 'Customer Information Verified',
        'salesperson' => 'Salesperson Verified',
        'work_order_po' => 'Work Order / PO Verified',
        'product_solution' => 'Product / Solution Verified',
        'sku' => 'SKU Verified',
        'quantity' => 'Quantity Verified',
        'subscription_type_period' => 'Subscription Type & Period Verified',
        'billing_type' => 'Billing Type Verified',
        'selling_price' => 'Selling Price Verified',
        'billing_green_signal' => 'Billing Green Signal Confirmed',
        'customer_outstanding' => 'Customer Outstanding Status Checked',
        'vendor_options' => 'Vendor Options Reviewed',
        'vendor_price_comparison' => 'Vendor Price Comparison Reviewed',
        'selected_vendor' => 'Selected Vendor Confirmed',
        'vendor_unit_cost' => 'Vendor Unit Cost Verified',
        'vat_tax_other_cost' => 'VAT / TAX / Additional Cost Verified',
        'final_landed_cost' => 'Final Landed Cost Verified',
        'expected_profit' => 'Expected Profit Verified',
        'margin_percent' => 'Margin % Verified',
        'low_margin_approval' => 'Low Margin Approval Checked (if required)',
        // Tenant / Account is no longer captured at Reviewer stage (change
        // request, Sept 2026) — it is entered order-wise, per item, during
        // Loading instead, so there is nothing to verify here any more.
        'loading_source' => 'Loading Source / Vendor Confirmed',
        'special_instructions' => 'Special Instructions Reviewed',
        'supporting_documents' => 'Supporting Documents Verified',
    ];

    public function __construct(
        private WorkflowService $workflow,
        private ApprovalService $approvals,
        private NotificationService $notifications,
        private AuditLogService $auditLog,
    ) {
    }

    public function index(Request $request)
    {
        Gate::denyIf(! $request->user()->can('review.view'));

        $requests = WorkRequest::with('customer', 'salesperson')
            ->when($request->filter === 'approved', fn ($q) => $q->where('status', 'REVIEWER_APPROVED'))
            ->when($request->filter === 'returned', fn ($q) => $q->whereIn('status', ['REVIEWER_RETURNED']))
            ->when(!$request->filter, fn ($q) => $q->where('status', 'REVIEWER_PENDING'))
            ->latest()->paginate(20);

        return view('reviewer.index', compact('requests'));
    }

    public function show(WorkRequest $request)
    {
        Gate::denyIf(! auth()->user()->can('review.view'));

        $approval = ReviewerApproval::firstOrCreate(['request_id' => $request->id]);

        if ($approval->checklists()->count() === 0) {
            foreach (self::CHECKLIST_ITEMS as $key => $label) {
                $approval->checklists()->create(['check_key' => $key, 'label' => $label, 'is_mandatory' => true]);
            }
        }

        $request->load('customer', 'salesperson', 'salesEntry', 'items.product', 'items.sku', 'items.vendorSelection.vendor', 'attachments');
        $approval->load('checklists', 'loadingSourceVendor');

        // Section 18's "Loading Source" is now a dropdown of the real Vendor
        // list (change request, Sept 2026) — which vendor will perform the
        // loading/installation, not the old free-text DIRECT_CSP/DISTRIBUTOR pair.
        $vendors = Vendor::where('status', 'ACTIVE')->orderBy('name')->get();

        return view('reviewer.show', compact('request', 'approval', 'vendors'));
    }

    public function saveChecklist(Request $httpRequest, WorkRequest $request)
    {
        $approval = $request->reviewerApproval;
        $checked = $httpRequest->input('checked', []);

        foreach ($approval->checklists as $item) {
            $item->update(['is_checked' => in_array((string) $item->id, $checked, true)]);
        }

        return back()->with('success', 'Checklist saved.');
    }

    public function vendorComparison(WorkRequest $request, RequestItem $item, VendorPriceService $vendorPriceService)
    {
        $rows = $vendorPriceService->compareForSku($item->sku, (float) $item->quantity, (float) $item->unit_selling_price);

        return view('reviewer.vendor-comparison', compact('request', 'item', 'rows'));
    }

    public function selectVendor(
        Request $httpRequest,
        WorkRequest $request,
        RequestItem $item,
        CostCalculationService $costCalc,
        MarginCalculationService $marginCalc,
        VendorPriceService $vendorPriceService,
    ) {
        $data = $httpRequest->validate([
            'vendor_product_price_id' => 'required|exists:vendor_product_prices,id',
            'override_reason' => 'nullable|string',
        ]);

        $price = VendorProductPrice::with('vendor')->findOrFail($data['vendor_product_price_id']);

        $cheapest = collect($vendorPriceService->compareForSku($item->sku, (float) $item->quantity, (float) $item->unit_selling_price))
            ->sortBy('final_landed_cost')->first();
        $isLowest = $cheapest && $cheapest['vendor_product_price_id'] === $price->id;

        if (! $isLowest && empty($data['override_reason'])) {
            return back()->withErrors(['override_reason' => 'A reason is required when selecting a vendor other than the lowest cost option (Rule 8).']);
        }

        $cost = $costCalc->landedCost(
            (float) $price->unit_purchase_price,
            (float) $item->quantity,
            (float) $price->vat_percent,
            (float) $price->tax_percent,
            (float) $price->handling_cost,
            (float) $price->delivery_cost,
            (float) $price->other_cost,
        );

        $sellingTotal = (float) $item->total_selling_price;

        DB::transaction(function () use ($item, $price, $cost, $sellingTotal, $isLowest, $data, $marginCalc) {
            VendorSelection::updateOrCreate(
                ['request_item_id' => $item->id],
                [
                    'vendor_id' => $price->vendor_id,
                    'vendor_product_price_id' => $price->id,
                    'unit_cost' => $price->unit_purchase_price,
                    'base_cost' => $cost['base_cost'],
                    'vat_amount' => $cost['vat_amount'],
                    'tax_amount' => $cost['tax_amount'],
                    'handling_cost' => $price->handling_cost,
                    'delivery_cost' => $price->delivery_cost,
                    'other_cost' => $price->other_cost,
                    'final_landed_cost' => $cost['final_landed_cost'],
                    'gross_profit' => $marginCalc->grossProfit($sellingTotal, $cost['final_landed_cost']),
                    'gross_margin_percent' => $marginCalc->grossMarginPercent($sellingTotal, $cost['final_landed_cost']),
                    'is_lowest_cost_vendor' => $isLowest,
                    'override_reason' => $data['override_reason'] ?? null,
                    'selected_by' => Auth::id(),
                ]
            );
        });

        $this->auditLog->record('Vendor Selection', 'Vendor selected for item', $item->request_id, null, $price->vendor->name.' @ '.$price->unit_purchase_price);

        return redirect()->route('reviewer.show', $request)->with('success', 'Vendor selected for '.($item->product->name ?? 'item').'.');
    }

    public function decide(Request $httpRequest, WorkRequest $request)
    {
        $data = $httpRequest->validate([
            'decision' => 'required|in:APPROVE,RETURN_TO_SALES,REJECT,HOLD',
            'remarks' => 'nullable|string',
            // Loading Source is now "which vendor will do the work" (change
            // request, Sept 2026) — required only when actually approving.
            // Tenant / Account is no longer collected on this screen; it is
            // captured order-wise, per item, during Loading instead.
            'loading_source_vendor_id' => 'required_if:decision,APPROVE|nullable|exists:vendors,id',
            'special_instructions' => 'nullable|string',
        ]);

        if ($data['decision'] !== 'APPROVE' && empty($data['remarks'])) {
            return back()->withErrors(['remarks' => 'Reason is mandatory for Return/Reject/Hold.']);
        }

        $approval = $request->reviewerApproval;

        if ($data['decision'] === 'APPROVE') {
            if (! $approval->allMandatoryChecked()) {
                return back()->withErrors(['checklist' => 'All mandatory checklist items must be checked before approval.']);
            }

            if ($request->items->contains(fn (RequestItem $i) => ! $i->vendorSelection)) {
                return back()->withErrors(['vendor' => 'Every item must have a vendor selected before approval.']);
            }

            $totalSelling = $request->totalSellingPrice();
            $totalCost = (float) $request->items->sum(fn ($i) => $i->vendorSelection->final_landed_cost);
            $totalVendorCost = (float) $request->items->sum(fn ($i) => $i->vendorSelection->base_cost);
            $totalTax = (float) $request->items->sum(fn ($i) => $i->vendorSelection->vat_amount + $i->vendorSelection->tax_amount);
            $totalOther = (float) $request->items->sum(fn ($i) => $i->vendorSelection->handling_cost + $i->vendorSelection->delivery_cost + $i->vendorSelection->other_cost);
            $profit = $totalSelling - $totalCost;
            $margin = $totalSelling > 0 ? round($profit / $totalSelling * 100, 4) : 0;

            $loadingSourceVendor = ! empty($data['loading_source_vendor_id'])
                ? Vendor::find($data['loading_source_vendor_id'])
                : null;

            $approval->update([
                'snapshot_total_selling_price' => $totalSelling,
                'snapshot_total_vendor_cost' => $totalVendorCost,
                'snapshot_total_tax' => $totalTax,
                'snapshot_total_other_cost' => $totalOther,
                'snapshot_final_landed_cost' => $totalCost,
                'snapshot_gross_profit' => $profit,
                'snapshot_gross_margin_percent' => $margin,
                'loading_source_vendor_id' => $loadingSourceVendor?->id,
                // Frozen text snapshot of the vendor's name at approval time
                // (Section 41's cost-snapshot principle) so this still reads
                // correctly even if the vendor is later renamed.
                'loading_source' => $loadingSourceVendor?->name,
                'special_instructions' => $data['special_instructions'] ?? null,
                'low_margin_approval_required' => $margin < (float) config('ilbc.minimum_margin_percent', 10),
                'decision' => 'APPROVE',
                'remarks' => $data['remarks'] ?? null,
                'decided_by' => Auth::id(),
                'decided_at' => now(),
            ]);

            $this->approvals->evaluate($request);

            if ($this->approvals->hasPendingApprovals($request)) {
                $this->notifications->notifyPermission('review.approve', 'Special approval required', "Request {$request->request_no} requires management/finance approval before Loading.", $request->id);
            }

            $this->workflow->transition($request, 'LOADING_PENDING', 'LOADING', 'APPROVE', $data['remarks'] ?? null);
            $this->notifications->notifyPermission('loading.process', 'Loading assigned', "Request {$request->request_no} is ready for Loading.", $request->id, route('loading.index'));
        } else {
            $approval->update(['decision' => $data['decision'], 'remarks' => $data['remarks'], 'decided_by' => Auth::id(), 'decided_at' => now()]);

            $statusMap = ['RETURN_TO_SALES' => 'REVIEWER_RETURNED', 'REJECT' => 'REVIEWER_RETURNED', 'HOLD' => 'REVIEWER_PENDING'];
            $stage = $data['decision'] === 'RETURN_TO_SALES' ? 'SALES' : 'REVIEWER';
            $this->workflow->transition($request, $statusMap[$data['decision']], $stage, $data['decision'], $data['remarks']);
        }

        $this->auditLog->record('Reviewer', $data['decision'], $request->id, null, $data['remarks'] ?? null);

        return redirect()->route('reviewer.index')->with('success', 'Decision recorded.');
    }
}
