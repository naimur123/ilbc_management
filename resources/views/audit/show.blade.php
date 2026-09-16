@extends('layouts.app')
@section('title', 'Audit - '.($item->request->request_no ?? ''))
@section('content')
@php($lr = $item->loadingRecord)
<h4 class="mb-3">Audit — {{ $item->request->request_no }}</h4>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="kpi-card h-100">
            <h6 class="text-primary">Request Summary</h6>
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Customer</th><td>{{ $item->request->customer->name ?? '-' }}</td></tr>
                <tr><th class="text-muted">Salesperson</th><td>{{ $item->request->salesperson->name ?? '-' }}</td></tr>
                <tr><th class="text-muted">Product / SKU</th><td>{{ $item->product->name ?? '-' }} / {{ $item->sku->sku_code ?? '-' }}</td></tr>
                <tr><th class="text-muted">Quantity</th><td>{{ $item->quantity }}</td></tr>
                <tr><th class="text-muted">Billing Period</th><td>{{ optional($item->start_date)->format('d-m-Y') }} - {{ optional($item->end_date)->format('d-m-Y') }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="kpi-card h-100">
            <h6 class="text-primary">Financial Summary</h6>
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Total Selling</th><td>{{ number_format($item->total_selling_price,2) }}</td></tr>
                <tr><th class="text-muted">Final Landed Cost</th><td>{{ $item->vendorSelection ? number_format($item->vendorSelection->final_landed_cost,2) : '-' }}</td></tr>
                <tr><th class="text-muted">Expected Profit</th><td>{{ $item->vendorSelection ? number_format($item->vendorSelection->gross_profit,2) : '-' }}</td></tr>
                <tr><th class="text-muted">Margin %</th><td>{{ $item->vendorSelection ? number_format($item->vendorSelection->gross_margin_percent,2).'%' : '-' }}</td></tr>
            </table>
        </div>
    </div>
</div>

{{-- SLA Before Audit (change request, Sept 2026): Target vs Actual, Met/Breached --}}
@if(count($slaSummary))
<div class="kpi-card mb-3">
    <h6 class="text-primary">SLA Status</h6>
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>SLA Type</th><th>Target</th><th>Actual Completion</th><th>Result</th></tr></thead>
        <tbody>
        @foreach($slaSummary as $row)
            <tr>
                <td>{{ ucfirst(strtolower($row['sla']->sla_type)) }} <span class="badge bg-light text-dark border">{{ str_replace('_',' ',$row['sla']->status) }}</span></td>
                <td>{{ intdiv($row['sla']->duration_minutes,60) }}h {{ $row['sla']->duration_minutes % 60 }}m — due {{ $row['sla']->target_at->format('d-m-Y h:i A') }}</td>
                <td>{{ $row['actual_duration'] ?? 'Not completed yet' }}</td>
                <td>
                    @if($row['met'] === true)<span class="text-success">✅ Met</span>
                    @elseif($row['met'] === false)<span class="text-danger">❌ Breached by {{ $row['variance'] }}</span>
                    @else<span class="text-muted">Pending</span>@endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @if($slaBlocksAudit)
        <div class="alert alert-warning mt-2 mb-0 py-2 small"><i class="bi bi-exclamation-triangle"></i> "SLA Completion Required Before Audit" is enabled and at least one SLA above is still open — Approve is disabled until it is completed or waived. <a href="{{ route('sla.index') }}">Go to SLA Management</a>.</div>
    @endif
</div>
@endif

{{-- Automatic Variance Detection (Section 23) --}}
<div class="kpi-card mb-3">
    <h6 class="text-primary">Automatic Variance Detection</h6>
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Field</th><th>Approved</th><th>Actual</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($variances as $v)
            <tr class="{{ $v['mismatch'] ? 'table-danger' : '' }}">
                <td>{{ $v['label'] }}</td><td>{{ $v['approved'] ?? '-' }}</td><td>{{ $v['actual'] ?? '-' }}</td>
                <td>{{ $v['mismatch'] ? 'MISMATCH' : 'OK' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="kpi-card mb-3">
    <ul class="nav nav-tabs" id="auditTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-loading">Loading Information</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-vendor">Vendor & Cost Information</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-instruction">Approved Instruction</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-docs">Documents</a></li>
    </ul>
    <div class="tab-content pt-3">
        <div class="tab-pane fade show active" id="tab-loading">
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Loading Date</th><td>{{ optional($lr?->loading_date)->format('d-m-Y') }}</td></tr>
                <tr><th class="text-muted">Actual Loaded Qty</th><td>{{ $lr->actual_loaded_quantity ?? '-' }}</td></tr>
                <tr><th class="text-muted">Subscription ID</th><td>{{ $lr->subscription_id ?? '-' }}</td></tr>
                <tr><th class="text-muted">License ID</th><td>{{ $lr->license_id ?? '-' }}</td></tr>
                <tr><th class="text-muted">Tenant / Account</th><td>{{ $lr->tenant_account ?? '-' }}</td></tr>
                <tr><th class="text-muted">Commiment Type</th><td>{{ $lr->commitmentType->name ?? '-' }}</td></tr>
                <tr><th class="text-muted">Billing Type</th><td>{{ $lr->billingType->name ?? '-' }}</td></tr>
                @if(!empty($lr->is_recurring))
                  <tr><th class="text-muted">Is Recurring</th><td>Yes</td></tr>
                @endif
                @if(!empty($lr->recurring_months))
                  <tr><th class="text-muted">Recurring Months</th><td>{{ $lr->recurring_months }}</td></tr>
                @endif
                <tr><th class="text-muted">Tenant / Account</th><td>{{ $lr->tenant_account ?? '-' }}</td></tr>
                <tr><th class="text-muted">Activation / Expiry</th><td>{{ optional($lr?->activation_date)->format('d-m-Y') }} / {{ optional($lr?->expiry_date)->format('d-m-Y') }}</td></tr>
                <tr><th class="text-muted">Technical Notes</th><td>{{ $lr->technical_notes ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="tab-pane fade" id="tab-vendor">
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Vendor</th><td>{{ $item->vendorSelection->vendor->name ?? '-' }}</td></tr>
                <tr><th class="text-muted">Unit Cost</th><td>{{ $item->vendorSelection->unit_cost ?? '-' }}</td></tr>
                <tr><th class="text-muted">VAT / Tax</th><td>{{ number_format(($item->vendorSelection->vat_amount ?? 0) + ($item->vendorSelection->tax_amount ?? 0),2) }}</td></tr>
                <tr><th class="text-muted">Final Landed Cost</th><td>{{ $item->vendorSelection->final_landed_cost ?? '-' }}</td></tr>
                <tr><th class="text-muted">Vendor Reference</th><td>{{ $lr->vendor_reference ?? '-' }}</td></tr>
                <tr><th class="text-muted">Distributor Reference</th><td>{{ $lr->distributor_reference ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="tab-pane fade" id="tab-instruction">
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Loading Source (Vendor)</th><td>{{ $item->request->reviewerApproval->loadingSourceVendor->name ?? $item->request->reviewerApproval->loading_source ?? '-' }}</td></tr>
                <tr><th class="text-muted">Tenant / Account (as loaded)</th><td>{{ $lr->tenant_account ?? '-' }}</td></tr>
                <tr><th class="text-muted">Special Instructions</th><td>{{ $item->request->reviewerApproval->special_instructions ?? '-' }}</td></tr>
                <tr><th class="text-muted">Reviewer</th><td>{{ $item->request->reviewerApproval->decidedBy->name ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="tab-pane fade" id="tab-docs">
            @forelse($lr?->attachments ?? [] as $a)
                <div class="small mb-1"><i class="bi bi-paperclip"></i> {{ $a->type }}: <a href="{{ \Illuminate\Support\Facades\Storage::url($a->path) }}" target="_blank">{{ $a->original_name }}</a></div>
            @empty
                <div class="text-muted small">No documents uploaded.</div>
            @endforelse
        </div>
    </div>
</div>

@can('audit.view')
<form action="{{ route('audit.decide', $item) }}" method="POST" class="kpi-card">
    @csrf
    <h6 class="text-primary">Audit Checklist</h6>
    <div class="row mb-3">
    @foreach($record->checklists as $c)
        <div class="col-md-6 form-check">
            <input type="checkbox" class="form-check-input" name="checked[]" value="{{ $c->id }}" id="ac{{ $c->id }}" {{ $c->is_checked ? 'checked' : '' }}>
            <label class="form-check-label small" for="ac{{ $c->id }}">{{ $c->label }}</label>
        </div>
    @endforeach
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Correction Category (if returning)</label>
            <select class="form-select select2-multiple" name="correction_category[]" multiple="multiple">
                @foreach(['Wrong Product','Wrong SKU','Wrong Quantity','Wrong Vendor','Wrong Tenant','Wrong Price','Cost Mismatch','Date Mismatch','Missing Proof','Duplicate Loading','Incomplete Loading','Other'] as $cat)
                    <option value="{{ $cat }}" {{ in_array($cat, (array) old('correction_category', $record->corrections->pluck('category')->toArray()), true) ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8"><label class="form-label">Remarks (mandatory for Return/Hold)</label><textarea class="form-control" name="remarks"></textarea></div>
    </div>
    <div class="mt-3">
        @can('audit.approve')<button class="btn btn-success" name="decision" value="APPROVE" {{ $slaBlocksAudit ? 'disabled' : '' }}>Approve & Send to Billing</button>@endcan
        @can('audit.return')<button class="btn btn-outline-secondary" name="decision" value="RETURN">Return to Loader for Correction</button>@endcan
        @can('audit.hold')<button class="btn btn-warning" name="decision" value="HOLD">Hold</button>@endcan
    </div>
</form>
@endcan
@endsection
@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2-multiple').select2({
            placeholder: "Select categories",
            allowClear: true
        });
    });
</script>
@endpush