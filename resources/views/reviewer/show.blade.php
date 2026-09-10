@extends('layouts.app')
@section('title', 'Review - '.$request->request_no)
@section('content')
<h4 class="mb-3">Reviewer Approval — {{ $request->request_no }}</h4>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="kpi-card h-100">
            <h6 class="text-primary">Customer & Order</h6>
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Customer</th><td>{{ $request->customer->name }}</td></tr>
                <tr><th class="text-muted">Salesperson</th><td>{{ $request->salesperson->name ?? '-' }}</td></tr>
                <tr><th class="text-muted">Work Order / PO</th><td>{{ $request->work_order_no ?: '-' }} / {{ $request->po_number ?: '-' }}</td></tr>
            </table>
            <h6 class="text-primary mt-2">Supporting Documents</h6>
            @forelse($request->attachments as $a)
                <div class="small"><i class="bi bi-paperclip"></i> <a href="{{ \Illuminate\Support\Facades\Storage::url($a->path) }}" target="_blank">{{ $a->original_name }}</a></div>
            @empty
                <div class="text-muted small">None uploaded.</div>
            @endforelse
        </div>
    </div>
    <div class="col-md-6">
        <div class="kpi-card h-100">
            <h6 class="text-primary">Financial Summary</h6>
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Total Selling Price</th><td class="fw-bold">{{ config('ilbc.currency_symbol') }} {{ number_format($request->totalSellingPrice(), 2) }}</td></tr>
                <tr><th class="text-muted">Final Landed Cost (all items)</th><td>{{ config('ilbc.currency_symbol') }} {{ number_format($request->items->sum(fn($i)=>$i->vendorSelection->final_landed_cost ?? 0), 2) }}</td></tr>
                @php($profit = $request->totalSellingPrice() - $request->items->sum(fn($i)=>$i->vendorSelection->final_landed_cost ?? 0))
                <tr><th class="text-muted">Expected Profit</th><td class="{{ $profit>=0?'text-success':'text-danger' }}">{{ config('ilbc.currency_symbol') }} {{ number_format($profit, 2) }}</td></tr>
                <tr><th class="text-muted">Margin %</th><td>{{ $request->totalSellingPrice() > 0 ? number_format($profit / $request->totalSellingPrice() * 100, 2) : '0.00' }}%</td></tr>
            </table>
        </div>
    </div>
</div>

@php($missingVendorItems = $request->items->filter(fn($i) => ! $i->vendorSelection))
<div class="kpi-card mb-3 p-0">
    <div class="p-3 pb-0">
        <h6 class="text-primary">Items — Vendor Selection</h6>
        @if($missingVendorItems->isNotEmpty())
            <div class="alert alert-warning py-2 px-3 mb-2 small">
                <i class="bi bi-exclamation-triangle"></i>
                This is the per-item cost/margin vendor (Section 12-13) — a separate thing from the "Loading Source" vendor below, which is who will physically do the loading.
                Click <strong>Compare Vendors</strong> and pick one for every row marked <strong>—</strong> below before you can Approve.
            </div>
        @endif
    </div>
    <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead class="table-light"><tr><th>Product / SKU</th><th>Qty</th><th>Selling</th><th>Selected Vendor</th><th>Unit Cost</th><th>Final Cost</th><th>Margin %</th><th></th></tr></thead>
        <tbody>
        @foreach($request->items as $item)
            <tr class="{{ ! $item->vendorSelection ? 'table-warning' : '' }}">
                <td>{{ $item->product->name ?? '-' }}<br><small class="text-muted">{{ $item->sku->sku_code ?? '' }}</small></td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->total_selling_price,2) }}</td>
                <td>{{ $item->vendorSelection->vendor->name ?? '—' }}
                    @if($item->vendorSelection && !$item->vendorSelection->is_lowest_cost_vendor)
                        <span class="badge bg-warning text-dark" title="{{ $item->vendorSelection->override_reason }}">Override</span>
                    @endif
                </td>
                <td>{{ $item->vendorSelection ? number_format($item->vendorSelection->unit_cost,2) : '—' }}</td>
                <td>{{ $item->vendorSelection ? number_format($item->vendorSelection->final_landed_cost,2) : '—' }}</td>
                <td>{{ $item->vendorSelection ? number_format($item->vendorSelection->gross_margin_percent,2).'%' : '—' }}</td>
                <td><a href="{{ route('requests.items.vendor-comparison', [$request, $item]) }}" class="btn btn-sm {{ $item->vendorSelection ? 'btn-outline-primary' : 'btn-primary' }}">Compare Vendors</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>

<div class="kpi-card mb-3">
    <h6 class="text-primary">Reviewer Checklist (Section 16)</h6>
    <form action="{{ route('reviewer.checklist', $request) }}" method="POST">
        @csrf
        <div class="row">
        @foreach($approval->checklists as $item)
            <div class="col-md-6">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="checked[]" value="{{ $item->id }}" id="chk{{ $item->id }}" {{ $item->is_checked ? 'checked' : '' }}>
                    <label class="form-check-label small" for="chk{{ $item->id }}">{{ $item->label }} @if($item->is_mandatory)<span class="text-danger">*</span>@endif</label>
                </div>
            </div>
        @endforeach
        </div>
        <button class="btn btn-sm btn-outline-primary mt-2">Save Checklist</button>
    </form>
    @if(!$approval->allMandatoryChecked())
        <div class="text-danger small mt-1">All mandatory items must be checked before you can Approve.</div>
    @endif
</div>

@can('review.approve')
<div class="kpi-card">
    <h6 class="text-primary">Decision</h6>
    <form action="{{ route('reviewer.decide', $request) }}" method="POST" class="row g-3">
        @csrf
        <div class="col-md-4"><label class="form-label">Loading Source (Vendor) *</label>
            <select class="form-select" name="loading_source_vendor_id">
                <option value="">Select Vendor</option>
                @foreach($vendors as $v)
                    <option value="{{ $v->id }}" {{ old('loading_source_vendor_id', $approval->loading_source_vendor_id) == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                @endforeach
            </select>
            <div class="form-text">Which vendor will perform the loading / installation once approved — separate from the per-item cost vendor above.</div>
        </div>
        <div class="col-md-8"></div>
        <div class="col-12"><label class="form-label">Special Instructions</label><textarea class="form-control" name="special_instructions"></textarea></div>
        <div class="col-12"><label class="form-label">Remarks (mandatory for Return/Reject/Hold)</label><textarea class="form-control" name="remarks"></textarea></div>
        @if($missingVendorItems->isNotEmpty())
            <div class="col-12"><div class="text-danger small">Approve is disabled until every item above has a cost vendor selected via "Compare Vendors".</div></div>
        @endif
        <div class="col-12">
            <button class="btn btn-success" name="decision" value="APPROVE" {{ $missingVendorItems->isNotEmpty() ? 'disabled' : '' }}><i class="bi bi-check-circle"></i> Approve</button>
            <button class="btn btn-outline-secondary" name="decision" value="RETURN_TO_SALES">Return to Sales</button>
            <button class="btn btn-danger" name="decision" value="REJECT">Reject</button>
            <button class="btn btn-warning" name="decision" value="HOLD">Hold</button>
        </div>
    </form>
</div>
@endcan
@endsection
