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
        <div class="col-md-3"><label class="form-label">Loading Date</label><input type="date" class="form-control" name="loading_date" value="{{ old('loading_date', $lr?->loading_date?->format('Y-m-d')) }}"></div>
        <div class="col-md-3"><label class="form-label">Loading Time</label><input type="time" class="form-control" name="loading_time" value="{{ old('loading_time', $lr?->loading_time) }}"></div>
        <div class="col-md-3"><label class="form-label">Actual Loaded Quantity</label><input type="number" step="0.01" class="form-control" name="actual_loaded_quantity" value="{{ old('actual_loaded_quantity', $lr?->actual_loaded_quantity) }}"></div>
        <div class="col-md-3"><label class="form-label">Tenant / Account</label><input class="form-control" name="tenant_account" value="{{ old('tenant_account', $lr?->tenant_account) }}"></div>
        <div class="col-md-3"><label class="form-label">Subscription ID</label><input class="form-control" name="subscription_id" value="{{ old('subscription_id', $lr?->subscription_id) }}"></div>
        <div class="col-md-3"><label class="form-label">License ID</label><input class="form-control" name="license_id" value="{{ old('license_id', $lr?->license_id) }}"></div>
        
        <div class="col-md-3">
            <label class="form-label">Commitment Type</label>
            <select class="form-select" name="commitment_type_id" id="commitment_type">
                <option value="" {{ old('commitment_type_id', $lr?->commitment_type_id) ? '' : 'selected' }}>-- Select --</option>
                @foreach($commitmentTypes as $ct)
                    <option value="{{ $ct->id }}" 
                        data-duration="{{ (stripos($ct->name,'annual') !== false) ? 'annual' : ((stripos($ct->name,'month') !== false) ? 'monthly' : '') }}" 
                        {{ (old('commitment_type_id', $lr?->commitment_type_id) == $ct->id) ? 'selected' : '' }}>
                        {{ $ct->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Billing Type</label>
            <select class="form-select" name="billing_type_id" id="billing_type">
                <option value="" {{ old('billing_type_id', $lr?->billing_type_id) ? '' : 'selected' }}>-- Select --</option>
                @foreach($billingTypes as $bt)
                    <option value="{{ $bt->id }}" 
                        data-frequency="{{ (stripos($bt->name,'month') !== false) ? 'monthly' : '' }}" 
                        {{ (old('billing_type_id', $lr?->billing_type_id) == $bt->id) ? 'selected' : '' }}>
                        {{ $bt->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 d-flex align-items-end" id="recurringContainer" style="display: none !important;">
            <div class="form-check p-2 px-3 bg-warning-subtle border border-warning rounded w-100 d-flex align-items-center gap-2">
                <input class="form-check-input mt-0 fs-5" type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ old('is_recurring', $lr?->is_recurring) ? 'checked' : '' }}>
                <label class="form-check-label fw-bold text-dark mb-0 cursor-pointer" for="is_recurring">
                    <i class="bi bi-arrow-repeat text-primary me-1"></i> Is Recurring?
                </label>
            </div>
        </div>

        <div class="col-md-3" id="recurringMonthsContainer" style="display: none;">
            <label class="form-label">Recurring Months</label>
            <input type="number" class="form-control" name="recurring_months" id="recurring_months" min="1" placeholder="e.g. 12" value="{{ old('recurring_months', $lr?->recurring_months) }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Activation Date</label>
            <input type="date" class="form-control" name="activation_date" id="activation_date" value="{{ old('activation_date', $lr?->activation_date?->format('Y-m-d')) }}">
        </div>

        <div class="col-md-3">
            <label class="form-label">Expiry Date</label>
            <input type="date" class="form-control" name="expiry_date" id="expiry_date" value="{{ old('expiry_date', $lr?->expiry_date?->format('Y-m-d')) }}">
        </div>

        <div class="col-md-4"><label class="form-label">Vendor Reference</label><input class="form-control" name="vendor_reference" value="{{ old('vendor_reference', $lr?->vendor_reference) }}"></div>
        <div class="col-md-4"><label class="form-label">Distributor Reference</label><input class="form-control" name="distributor_reference" value="{{ old('distributor_reference', $lr?->distributor_reference) }}"></div>
        <div class="col-md-4"><label class="form-label">PO Reference</label><input class="form-control" name="po_reference" value="{{ old('po_reference', $lr?->po_reference) }}"></div>
        <div class="col-12"><label class="form-label">Technical Notes</label><textarea class="form-control" name="technical_notes">{{ old('technical_notes', $lr?->technical_notes) }}</textarea></div>
    </div>
</div>

<div class="kpi-card mb-3">
    <h6 class="text-primary">Documents</h6>
    <div class="row g-3">
        <div class="col-md-3"><label class="form-label">Screenshot</label><input type="file" class="form-control" name="screenshot"></div>
        <div class="col-md-3"><label class="form-label">Vendor Confirmation</label><input type="file" class="form-control" name="vendor_confirmation"></div>
        <div class="col-md-3"><label class="form-label">CSP Screenshot</label><input type="file" class="form-control" name="csp_screenshot"></div>
        <div class="col-md-3"><label class="form-label">Subscription Confirmation</label><input type="file" class="form-control" name="subscription_confirmation"></div>
        <div class="col-md-3"><label class="form-label">SLA Document</label><input type="file" class="form-control" name="sla_document"></div>
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

<script>
(function(){
    const billingSelect = document.getElementById('billing_type');
    const commitmentSelect = document.getElementById('commitment_type');
    const activationInput = document.getElementById('activation_date');
    const expiryInput = document.getElementById('expiry_date');
    const recurringContainer = document.getElementById('recurringContainer');
    const isRecurringCheckbox = document.getElementById('is_recurring');
    const recurringMonthsContainer = document.getElementById('recurringMonthsContainer');

    function updateRecurringVisibility(){
        if(!billingSelect || !recurringContainer) return;
        
        const opt = billingSelect.options[billingSelect.selectedIndex];
        const freq = opt?.dataset?.frequency;

        if(freq === 'monthly'){
            recurringContainer.style.setProperty('display', 'flex', 'important');
        } else {
            recurringContainer.style.setProperty('display', 'none', 'important');
            if(isRecurringCheckbox) isRecurringCheckbox.checked = false;
        }
        toggleRecurringMonths();
    }

    function toggleRecurringMonths(){
        if(!recurringMonthsContainer) return;

        if(isRecurringCheckbox && isRecurringCheckbox.checked && recurringContainer.style.display !== 'none'){
            recurringMonthsContainer.style.display = 'block';
        } else {
            recurringMonthsContainer.style.display = 'none';
        }
    }

    function addMonths(dateString, months){
        const parts = dateString.split('-');
        if(parts.length !== 3) return null;

        const year = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1;
        const day = parseInt(parts[2], 10);

        const d = new Date(year, month + months, day);
        if (d.getDate() !== day) {
            d.setDate(0);
        } else {
            d.setDate(d.getDate() - 1);
        }
        return d;
    }

    function formatDate(d){
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    }

    function updateExpiry(){
        const act = activationInput?.value;
        if(!act || !commitmentSelect) {
            if (!act && expiryInput) expiryInput.value = '';
            return;
        }

        const opt = commitmentSelect.options[commitmentSelect.selectedIndex];
        const duration = opt?.dataset?.duration;

        let monthsToAdd = 0;
        if(duration === 'annual'){
            monthsToAdd = 12;
        } else if(duration === 'monthly'){
            monthsToAdd = 1;
        }

        if(monthsToAdd > 0){
            const calculatedDate = addMonths(act, monthsToAdd);
            if(calculatedDate){
                expiryInput.value = formatDate(calculatedDate);
            }
        }
    }

    function handleCommitmentChange(){
        if(activationInput) activationInput.value = '';
        if(expiryInput) expiryInput.value = '';
    }

    if(billingSelect){
        billingSelect.addEventListener('change', updateRecurringVisibility);
    }
    if(isRecurringCheckbox){
        isRecurringCheckbox.addEventListener('change', toggleRecurringMonths);
    }
    if(commitmentSelect){
        commitmentSelect.addEventListener('change', function(){
            handleCommitmentChange();
            updateExpiry();
        });
    }
    if(activationInput){
        activationInput.addEventListener('change', updateExpiry);
    }

    updateRecurringVisibility();
    updateExpiry();
})();
</script>
@endsection