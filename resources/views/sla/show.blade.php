@extends('layouts.app')
@section('title', 'SLA - '.($requestSla->request->request_no ?? ''))
@section('content')
<h4 class="mb-3">SLA — {{ $requestSla->request->request_no }} <small class="text-muted">({{ ucfirst(strtolower($requestSla->sla_type)) }})</small></h4>

@php($badge = match($requestSla->status) {
    'PENDING' => 'bg-secondary', 'ACTIVE' => 'bg-success', 'DUE_SOON' => 'bg-warning text-dark',
    'OVERDUE' => 'bg-danger', 'COMPLETED_WITHIN_SLA' => 'bg-success', 'COMPLETED_LATE' => 'bg-warning text-dark',
    'WAIVED' => 'bg-info text-dark', default => 'bg-light text-dark border',
})

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="kpi-card h-100">
            <h6 class="text-primary">SLA Details</h6>
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Request</th><td><a href="{{ route('requests.show', $requestSla->request) }}">{{ $requestSla->request->request_no }}</a></td></tr>
                <tr><th class="text-muted">Customer</th><td>{{ $requestSla->request->customer->name ?? '-' }}</td></tr>
                <tr><th class="text-muted">Product / SKU</th><td>{{ $requestSla->requestItem->product->name ?? 'All items' }}</td></tr>
                <tr><th class="text-muted">SLA Type</th><td>{{ ucfirst(strtolower($requestSla->sla_type)) }}</td></tr>
                <tr><th class="text-muted">Priority</th><td>{{ ucfirst(strtolower($requestSla->priority)) }}</td></tr>
                <tr><th class="text-muted">Configuration Used</th><td>{{ $requestSla->configuration->name ?? 'Default (no matching rule configured)' }}</td></tr>
                <tr><th class="text-muted">Status</th><td><span class="badge {{ $badge }}">{{ str_replace('_',' ',$requestSla->status) }}</span></td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="kpi-card h-100">
            <h6 class="text-primary">Timing</h6>
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">SLA Start</th><td>{{ $requestSla->start_at->format('d-m-Y h:i A') }}</td></tr>
                <tr><th class="text-muted">SLA Target</th><td>{{ $requestSla->target_at->format('d-m-Y h:i A') }}</td></tr>
                <tr><th class="text-muted">SLA Duration</th><td>{{ intdiv($requestSla->duration_minutes, 60) }}h {{ $requestSla->duration_minutes % 60 }}m</td></tr>
                <tr><th class="text-muted">Responsible</th><td>{{ $requestSla->responsibleUser->name ?? $requestSla->responsible_team ?? 'Unassigned' }}</td></tr>
                <tr><th class="text-muted">Completion Date & Time</th><td>{{ optional($requestSla->completed_at)->format('d-m-Y h:i A') ?? '-' }}</td></tr>
                @if($requestSla->completed_at)
                <tr><th class="text-muted">Result</th><td>
                    @if($requestSla->status === 'COMPLETED_WITHIN_SLA')
                        <span class="text-success">✅ Met — completed {{ $requestSla->target_at->diffForHumans($requestSla->completed_at, true) }} before deadline</span>
                    @else
                        <span class="text-danger">❌ Breached by {{ $requestSla->target_at->diffForHumans($requestSla->completed_at, true) }}</span>
                    @endif
                </td></tr>
                @endif
            </table>
        </div>
    </div>
</div>

@if($requestSla->remarks || $requestSla->waived_reason)
<div class="kpi-card mb-3">
    <h6 class="text-primary">Remarks</h6>
    @if($requestSla->remarks)<p class="mb-1">{{ $requestSla->remarks }}</p>@endif
    @if($requestSla->waived_reason)<p class="mb-0 text-info"><strong>Waived:</strong> {{ $requestSla->waived_reason }} — by {{ $requestSla->waivedBy->name ?? '-' }} on {{ optional($requestSla->waived_at)->format('d-m-Y H:i') }}</p>@endif
</div>
@endif

@if($requestSla->attachment_path)
<div class="kpi-card mb-3">
    <h6 class="text-primary">Supporting Document</h6>
    <a href="{{ \Illuminate\Support\Facades\Storage::url($requestSla->attachment_path) }}" target="_blank"><i class="bi bi-paperclip"></i> {{ $requestSla->attachment_original_name }}</a>
</div>
@endif

@if(! $requestSla->isFinal())
@can('sla.manage')
<div class="kpi-card mb-3">
    <h6 class="text-primary">Mark Completed</h6>
    <form action="{{ route('sla.complete', $requestSla) }}" method="POST" enctype="multipart/form-data" class="row g-3">
        @csrf
        <div class="col-md-6"><label class="form-label">SLA Remarks</label><textarea class="form-control" name="remarks"></textarea></div>
        <div class="col-md-6"><label class="form-label">Supporting Document</label><input type="file" class="form-control" name="attachment" accept=".pdf,.jpg,.jpeg,.png"></div>
        <div class="col-12"><button class="btn btn-success btn-sm">Mark Completed</button></div>
    </form>
</div>
@endcan
@can('sla.waive')
<div class="kpi-card mb-3">
    <h6 class="text-primary">Waive / Exception</h6>
    <form action="{{ route('sla.waive', $requestSla) }}" method="POST" class="row g-3">
        @csrf
        <div class="col-md-9"><label class="form-label">Reason (mandatory, management-approved exception)</label><input class="form-control" name="waived_reason" required></div>
        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-outline-warning btn-sm w-100">Waive SLA</button></div>
    </form>
</div>
@endcan
@endif

<div class="kpi-card">
    <h6 class="text-primary">Status History</h6>
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>When</th><th>From</th><th>To</th><th>By</th><th>Remarks</th></tr></thead>
        <tbody>
        @forelse($requestSla->statusHistory as $h)
            <tr>
                <td>{{ $h->created_at->format('d-m-Y H:i') }}</td>
                <td>{{ $h->from_status ? str_replace('_',' ',$h->from_status) : '-' }}</td>
                <td>{{ str_replace('_',' ',$h->to_status) }}</td>
                <td>{{ $h->changedBy->name ?? 'System' }}</td>
                <td>{{ $h->remarks }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-3">No history yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<a href="{{ route('sla.index') }}" class="btn btn-link mt-3">Back to SLA Management</a>
@endsection
