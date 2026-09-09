@extends('layouts.app')
@section('title', 'Loading Review - '.$request->request_no)
@section('content')
<h4 class="mb-3">Loading / Installation — Review {{ $request->request_no }}</h4>

@if(count($timeline))
<div class="kpi-card mb-3">
    <div class="d-flex flex-wrap">
        @foreach($timeline as $t)
            <div class="timeline-step timeline-{{ strtolower($t['state']) }}">
                <div class="timeline-dot mx-auto"><i class="bi {{ $t['state'] === 'DONE' ? 'bi-check-lg' : ($t['state'] === 'CURRENT' ? 'bi-arrow-right' : 'bi-hourglass') }}"></i></div>
                <div class="small mt-1 fw-semibold">{{ $t['step']->name }}</div>
                <div class="small text-muted">{{ $t['state'] }}</div>
            </div>
        @endforeach
    </div>
</div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="kpi-card h-100">
            <h6 class="text-primary">Request Summary</h6>
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Customer</th><td>{{ $request->customer->name ?? '-' }}</td></tr>
                <tr><th class="text-muted">Salesperson</th><td>{{ $request->salesperson->name ?? '-' }}</td></tr>
                <tr><th class="text-muted">Loading Source (Vendor)</th><td>{{ $request->reviewerApproval?->loadingSourceVendor->name ?? $request->reviewerApproval?->loading_source ?? '-' }}</td></tr>
                <tr><th class="text-muted">Special Instructions</th><td>{{ $request->reviewerApproval?->special_instructions ?: '-' }}</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="kpi-card h-100">
            <h6 class="text-primary">Approval</h6>
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Reviewer</th><td>{{ $request->reviewerApproval?->decidedBy->name ?? '-' }}</td></tr>
                <tr><th class="text-muted">Approval Date</th><td>{{ optional($request->reviewerApproval?->decided_at)->format('d-m-Y H:i') ?? '-' }}</td></tr>
                <tr><th class="text-muted">Status</th><td><span class="badge bg-light text-dark border">{{ str_replace('_',' ',$request->status) }}</span></td></tr>
            </table>
        </div>
    </div>
</div>

<div class="kpi-card mb-3 p-0">
    <div class="p-3 pb-0">
        <h6 class="text-primary">Customer-wise / Order-wise Vendor & SKU Assignment</h6>
        <p class="small text-muted mb-0">Vendor and cost per line are locked from Reviewer approval (Section 41). Assign the Tenant / Account each item will be loaded under, order line by order line, then open each item to enter full Loading details, documents and checklist.</p>
    </div>
    @can('loading.process')
    <form action="{{ route('loading.assign', $request) }}" method="POST">
    @csrf
    @endcan
    <div class="table-responsive mt-2">
    <table class="table table-sm align-middle mb-0">
        <thead class="table-light"><tr><th>Product / SKU</th><th>Qty</th><th>Vendor</th><th style="min-width:200px">Tenant / Account</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach($request->items as $item)
            @php($lr = $item->loadingRecord)
            <tr>
                <td>{{ $item->product->name ?? '-' }}<br><small class="text-muted">{{ $item->sku->sku_code ?? '' }}</small></td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->vendorSelection->vendor->name ?? '-' }}</td>
                <td>
                    @can('loading.process')
                    <input type="text" class="form-control form-control-sm" name="tenant_account[{{ $item->id }}]" value="{{ old('tenant_account.'.$item->id, $lr->tenant_account ?? '') }}" placeholder="Tenant / Account">
                    @else
                        {{ $lr->tenant_account ?? '-' }}
                    @endcan
                </td>
                <td>
                    @if($lr?->status === 'COMPLETED')<span class="badge bg-success">Completed</span>
                    @elseif($lr)<span class="badge bg-warning text-dark">In Progress</span>
                    @else<span class="badge bg-secondary">Not Started</span>@endif
                </td>
                <td><a href="{{ route('loading.show', $item) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    @can('loading.process')
    <div class="p-3">
        <button class="btn btn-primary btn-sm">Save Assignment</button>
    </div>
    </form>
    @endcan
</div>

@if($request->items->isNotEmpty() && $request->items->every(fn($i) => $i->loadingRecord?->status === 'COMPLETED'))
    <div class="alert alert-success">All items loaded — this request has moved on to Audit.</div>
@else
    <div class="alert alert-info">Open each item above to enter Loading details, documents and complete its checklist. Once every item is marked "Mark Loading Completed", this request moves to Audit automatically.</div>
@endif

<a href="{{ route('loading.index') }}" class="btn btn-link">Back to Loading Queue</a>
@endsection
