@extends('layouts.app')
@section('title', 'Loading - '.($item->request->request_no ?? ''))
@section('content')
@php($lr = $item->loadingRecord)
<h4 class="mb-3">Loading — {{ $item->request->request_no }}</h4>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="kpi-card h-100">
            <h6 class="text-primary">Approved Loading Instruction (Read-Only)</h6>
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Vendor</th><td>{{ $item->vendorSelection->vendor->name ?? '-' }}</td></tr>
                <tr><th class="text-muted">Product</th><td>{{ $item->product->name ?? '-' }}</td></tr>
                <tr><th class="text-muted">SKU</th><td>{{ $item->sku->sku_code ?? '-' }}</td></tr>
                <tr><th class="text-muted">Quantity</th><td>{{ $item->quantity }}</td></tr>
                <tr><th class="text-muted">Loading Source (Vendor)</th><td>{{ $item->request->reviewerApproval->loadingSourceVendor->name ?? $item->request->reviewerApproval->loading_source ?? '-' }}</td></tr>
                <tr><th class="text-muted">Subscription Type</th><td>{{ $item->subscriptionType->name ?? '-' }}</td></tr>
                <tr><th class="text-muted">Start / End</th><td>{{ optional($item->start_date)->format('d-m-Y') }} - {{ optional($item->end_date)->format('d-m-Y') }}</td></tr>
                <tr><th class="text-muted">Special Instruction</th><td>{{ $item->request->reviewerApproval->special_instructions ?? '-' }}</td></tr>
                <tr><th class="text-muted">Reviewer</th><td>{{ $item->request->reviewerApproval->decidedBy->name ?? '-' }} on {{ optional($item->request->reviewerApproval->decided_at)->format('d-m-Y H:i') }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="kpi-card h-100">
            <h6 class="text-primary">Sales & Financial Overview</h6>
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Unit Selling Price</th><td>{{ config('ilbc.currency_symbol') }} {{ number_format($item->unit_selling_price,2) }}</td></tr>
                <tr><th class="text-muted">Total Selling</th><td>{{ config('ilbc.currency_symbol') }} {{ number_format($item->total_selling_price,2) }}</td></tr>
                @can('sales.view_cost')
                <tr><th class="text-muted">Final Landed Cost</th><td>{{ $item->vendorSelection ? number_format($item->vendorSelection->final_landed_cost,2) : '-' }}</td></tr>
                @endcan
                @can('sales.view_margin')
                <tr><th class="text-muted">Expected Profit</th><td>{{ $item->vendorSelection ? number_format($item->vendorSelection->gross_profit,2) : '-' }}</td></tr>
                <tr><th class="text-muted">Margin %</th><td>{{ $item->vendorSelection ? number_format($item->vendorSelection->gross_margin_percent,2).'%' : '-' }}</td></tr>
                @endcan
            </table>
            <p class="small text-muted mb-0">Commercial figures are locked — the Loader cannot change approval information here.</p>
        </div>
    </div>
</div>

@can('loading.process')
<form action="{{ route('loading.draft', $item) }}" method="POST" enctype="multipart/form-data" id="loadingForm">
@csrf
<div class="kpi-card mb-3">
    <h6 class="text-primary">Loading Details</h6>
    <div class="row g-3">
        <div class="col-md-3"><label class="form-label">Loading Date</label><input type="date" class="form-control" name="loading_date" value="{{ old('loading_date', $lr->loading_date?->format('Y-m-d')) }}"></div>
        <div class="col-md-3"><label class="form-label">Loading Time</label><input type="time" class="form-control" name="loading_time" value="{{ old('loading_time', $lr->loading_time) }}"></div>
        <div class="col-md-3"><label class="form-label">Actual Loaded Quantity</label><input type="number" step="0.01" class="form-control" name="actual_loaded_quantity" value="{{ old('actual_loaded_quantity', $lr->actual_loaded_quantity) }}"></div>
        <div class="col-md-3"><label class="form-label">Tenant / Account</label><input class="form-control" name="tenant_account" value="{{ old('tenant_account', $lr->tenant_account) }}"></div>
        <div class="col-md-3"><label class="form-label">Subscription ID</label><input class="form-control" name="subscription_id" value="{{ old('subscription_id', $lr->subscription_id) }}"></div>
        <div class="col-md-3"><label class="form-label">License ID</label><input class="form-control" name="license_id" value="{{ old('license_id', $lr->license_id) }}"></div>
        <div class="col-md-3"><label class="form-label">Activation Date</label><input type="date" class="form-control" name="activation_date" value="{{ old('activation_date', $lr->activation_date?->format('Y-m-d')) }}"></div>
        <div class="col-md-3"><label class="form-label">Expiry Date</label><input type="date" class="form-control" name="expiry_date" value="{{ old('expiry_date', $lr->expiry_date?->format('Y-m-d')) }}"></div>
        <div class="col-md-4"><label class="form-label">Vendor Reference</label><input class="form-control" name="vendor_reference" value="{{ old('vendor_reference', $lr->vendor_reference) }}"></div>
        <div class="col-md-4"><label class="form-label">Distributor Reference</label><input class="form-control" name="distributor_reference" value="{{ old('distributor_reference', $lr->distributor_reference) }}"></div>
        <div class="col-md-4"><label class="form-label">PO Reference</label><input class="form-control" name="po_reference" value="{{ old('po_reference', $lr->po_reference) }}"></div>
        <div class="col-12"><label class="form-label">Technical Notes</label><textarea class="form-control" name="technical_notes">{{ old('technical_notes', $lr->technical_notes) }}</textarea></div>
    </div>
</div>

<div class="kpi-card mb-3">
    <h6 class="text-primary">Documents</h6>
    <div class="row g-3">
        <div class="col-md-3"><label class="form-label">Screenshot</label><input type="file" class="form-control" name="screenshot"></div>
        <div class="col-md-3"><label class="form-label">Vendor Confirmation</label><input type="file" class="form-control" name="vendor_confirmation"></div>
        <div class="col-md-3"><label class="form-label">CSP Screenshot</label><input type="file" class="form-control" name="csp_screenshot"></div>
        <div class="col-md-3"><label class="form-label">Subscription Confirmation</label><input type="file" class="form-control" name="subscription_confirmation"></div>
    </div>
    @if($lr && $lr->attachments->count())
    <div class="mt-2">
        @foreach($lr->attachments as $a)
            <span class="badge bg-light text-dark border me-1"><i class="bi bi-paperclip"></i> {{ $a->type }}: <a href="{{ \Illuminate\Support\Facades\Storage::url($a->path) }}" target="_blank">{{ $a->original_name }}</a></span>
        @endforeach
    </div>
    @endif
</div>

@if($lr)
<div class="kpi-card mb-3">
    <h6 class="text-primary">Loading Checklist</h6>
    <div class="row">
    @foreach($lr->checklists as $c)
        <div class="col-md-6 form-check">
            <input type="checkbox" class="form-check-input" name="checked[]" value="{{ $c->id }}" id="lc{{ $c->id }}" {{ $c->is_checked ? 'checked' : '' }}>
            <label class="form-check-label small" for="lc{{ $c->id }}">{{ $c->label }}</label>
        </div>
    @endforeach
    </div>
</div>
@endif

<div class="mb-4">
    <button type="submit" class="btn btn-outline-secondary">Save Draft</button>
    @can('loading.complete')
    @if($lr?->status !== 'COMPLETED')
    <button type="submit" formaction="{{ route('loading.complete', $item) }}" class="btn btn-primary">Mark Loading Completed</button>
    @endif
    @endcan
    <a href="{{ route('loading.index') }}" class="btn btn-link">Back</a>
</div>
</form>
@endcan
@endsection
