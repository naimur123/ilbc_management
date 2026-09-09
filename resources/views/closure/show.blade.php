@extends('layouts.app')
@section('title', 'Closure - '.$request->request_no)
@section('content')
<h4 class="mb-3">Closure — {{ $request->request_no }}</h4>

<div class="kpi-card mb-3">
    <h6 class="text-primary">Final Summary</h6>
    <table class="table table-sm mb-0">
        <tr><th class="text-muted">Total Selling</th><td>{{ config('ilbc.currency_symbol') }} {{ number_format($request->totalSellingPrice(),2) }}</td></tr>
        <tr><th class="text-muted">Vendor Cost</th><td>{{ config('ilbc.currency_symbol') }} {{ number_format($request->items->sum(fn($i)=>$i->vendorSelection->final_landed_cost ?? 0),2) }}</td></tr>
        <tr><th class="text-muted">All Items Loaded</th><td>{!! $request->items->every(fn($i) => $i->loadingRecord?->status === 'COMPLETED') ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' !!}</td></tr>
        <tr><th class="text-muted">All Items Audit Approved</th><td>{!! $request->items->every(fn($i) => $i->auditRecord?->decision === 'APPROVE') ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' !!}</td></tr>
        <tr><th class="text-muted">Invoice Generated</th><td>{!! $request->billingRecord?->invoice_generated_at ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' !!}</td></tr>
        <tr><th class="text-muted">Invoice Sent</th><td>{!! $request->billingRecord?->invoice_sent_at ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' !!}</td></tr>
        <tr><th class="text-muted">Billing Done</th><td>{!! $request->billingRecord?->billing_done_at ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' !!}</td></tr>
        <tr><th class="text-muted">Collection</th><td>{!! $request->billingRecord?->collection_status === 'RECEIVED' ? '<span class="badge bg-success">Received</span>' : ($request->billingRecord?->collection_status === 'PARTIAL' ? '<span class="badge bg-warning text-dark">Partial</span>' : '<span class="badge bg-danger">Pending</span>') !!}</td></tr>
    </table>
</div>

@if(! $request->closure)
@can('closure.close')
<div class="kpi-card mb-3">
    <h6 class="text-primary">Collection Details</h6>
    <form action="{{ route('closure.collection', $request) }}" method="POST" class="row g-3">
        @csrf
        @php($br = $request->billingRecord)
        <div class="col-md-3"><label class="form-label">Collection Status</label>
            <select class="form-select" name="collection_status">
                <option value="">-</option>
                <option value="PENDING" {{ $br?->collection_status === 'PENDING' ? 'selected' : '' }}>Pending</option>
                <option value="PARTIAL" {{ $br?->collection_status === 'PARTIAL' ? 'selected' : '' }}>Partial</option>
                <option value="RECEIVED" {{ $br?->collection_status === 'RECEIVED' ? 'selected' : '' }}>Received</option>
            </select>
        </div>
        <div class="col-md-3"><label class="form-label">Collection Amount</label><input type="number" step="0.01" class="form-control" name="collection_amount" value="{{ $br?->collection_amount }}"></div>
        <div class="col-md-3"><label class="form-label">Collection Date</label><input type="date" class="form-control" name="collection_date" value="{{ optional($br?->collection_date)->format('Y-m-d') }}"></div>
        <div class="col-md-3"><label class="form-label">Outstanding Amount</label><input type="number" step="0.01" class="form-control" name="outstanding_amount" value="{{ $br?->outstanding_amount }}"></div>
        <div class="col-md-4"><label class="form-label">Payment Reference</label><input class="form-control" name="payment_reference" value="{{ $br?->payment_reference }}"></div>
        <div class="col-md-4"><label class="form-label">Bank / Payment Method</label><input class="form-control" name="payment_method" value="{{ $br?->payment_method }}"></div>
        <div class="col-md-4 d-flex align-items-end"><button class="btn btn-outline-primary btn-sm">Save Collection Details</button></div>
    </form>
</div>

<div class="kpi-card mb-3">
    <h6 class="text-primary">Closure Checklist (Section 10)</h6>
    <form action="{{ route('closure.checklist', $request) }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="text-muted small fw-semibold mb-1">Billing Verification</div>
                @foreach($request->closureChecklists->filter(fn($c) => str_starts_with($c->check_key, 'invoice_') || $c->check_key === 'vat_tax_verified' || $c->check_key === 'billing_document_attached') as $c)
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="checked[]" value="{{ $c->id }}" id="cc{{ $c->id }}" {{ $c->is_checked ? 'checked' : '' }}>
                        <label class="form-check-label small" for="cc{{ $c->id }}">{{ $c->label }} @if($c->is_mandatory)<span class="text-danger">*</span>@endif</label>
                    </div>
                @endforeach
            </div>
            <div class="col-md-6">
                <div class="text-muted small fw-semibold mb-1">Collection Verification</div>
                @foreach($request->closureChecklists->filter(fn($c) => str_starts_with($c->check_key, 'collection_') || in_array($c->check_key, ['payment_reference_entered','payment_method_recorded','outstanding_amount_calculated'])) as $c)
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="checked[]" value="{{ $c->id }}" id="cc{{ $c->id }}" {{ $c->is_checked ? 'checked' : '' }}>
                        <label class="form-check-label small" for="cc{{ $c->id }}">{{ $c->label }} @if($c->is_mandatory)<span class="text-danger">*</span>@endif</label>
                    </div>
                @endforeach
            </div>
        </div>
        <button class="btn btn-sm btn-outline-primary mt-2">Save Checklist</button>
    </form>
    @if($request->closureChecklists->where('is_mandatory', true)->where('is_checked', false)->isNotEmpty())
        <div class="text-danger small mt-2">All checklist items above must be checked before the request can be closed.</div>
    @endif
</div>
@endcan
@endif

@if($request->closure)
<div class="alert alert-success">Closed by {{ $request->closure->closedBy->name ?? '-' }} on {{ $request->closure->closure_date->format('d-m-Y') }} {{ $request->closure->closure_time }}.<br>{{ $request->closure->remarks }}</div>
@elseif($canClose)
@can('closure.close')
<form action="{{ route('closure.close', $request) }}" method="POST" class="kpi-card">
    @csrf
    <label class="form-label">Closure Remarks</label>
    <textarea class="form-control mb-3" name="remarks"></textarea>
    <button class="btn btn-primary">Close Request</button>
</form>
@endcan
@else
<div class="alert alert-warning">This request cannot be closed yet — Loading, Audit, Invoice Generated/Sent, Billing Done and the Closure Checklist above must all be complete first.</div>
@endif

@if($request->reopenRequests->isNotEmpty())
<div class="kpi-card mt-3">
    <h6 class="text-primary">Reopen Requests</h6>
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Requested By</th><th>Target Stage</th><th>Reason</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach($request->reopenRequests as $rr)
            <tr>
                <td>{{ $rr->requestedBy->name ?? '-' }}</td><td>{{ str_replace('_',' ',$rr->target_stage) }}</td><td>{{ $rr->reason }}</td>
                <td><span class="badge bg-light text-dark border">{{ $rr->status }}</span></td>
                <td>
                    @can('closure.reopen')
                    @if($rr->status === 'PENDING')
                    <form action="{{ route('requests.reopen.approve', [$request, $rr]) }}" method="POST">@csrf<button class="btn btn-sm btn-outline-primary">Approve Reopen</button></form>
                    @endif
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
