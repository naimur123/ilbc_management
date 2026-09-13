@extends('layouts.app')
@section('title', 'SLA Management')
@section('content')
<h4 class="mb-3">SLA Management</h4>

<ul class="nav nav-pills mb-3 flex-wrap">
    <li class="nav-item"><a class="nav-link {{ !$filter ? 'active' : '' }}" href="{{ route('sla.index') }}">Open</a></li>
    <li class="nav-item"><a class="nav-link {{ $filter==='pending' ? 'active' : '' }}" href="{{ route('sla.index', ['filter'=>'pending']) }}">Pending</a></li>
    <li class="nav-item"><a class="nav-link {{ $filter==='active' ? 'active' : '' }}" href="{{ route('sla.index', ['filter'=>'active']) }}">Active</a></li>
    <li class="nav-item"><a class="nav-link {{ $filter==='due_soon' ? 'active' : '' }}" href="{{ route('sla.index', ['filter'=>'due_soon']) }}">Due Soon</a></li>
    <li class="nav-item"><a class="nav-link {{ $filter==='overdue' ? 'active' : '' }}" href="{{ route('sla.index', ['filter'=>'overdue']) }}">Overdue</a></li>
    <li class="nav-item"><a class="nav-link {{ $filter==='completed' ? 'active' : '' }}" href="{{ route('sla.index', ['filter'=>'completed']) }}">Completed</a></li>
    <li class="nav-item"><a class="nav-link {{ $filter==='exceptions' ? 'active' : '' }}" href="{{ route('sla.index', ['filter'=>'exceptions']) }}">Exceptions</a></li>
    @can('sla.report')<li class="nav-item ms-auto"><a class="nav-link" href="{{ route('sla.report') }}"><i class="bi bi-graph-up"></i> SLA Report</a></li>@endcan
    @can('sla.configure')<li class="nav-item"><a class="nav-link" href="{{ route('sla.configuration') }}"><i class="bi bi-gear"></i> Configuration</a></li>@endcan
</ul>

<div class="kpi-card p-0 mb-3">
    <div class="table-responsive">
    <table class="table table-sm table-hover mb-0 align-middle">
        <thead class="table-light"><tr><th>Request</th><th>Customer</th><th>SLA</th><th>Priority</th><th>Owner</th><th>Deadline</th><th>Remaining</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($slas as $sla)
            @php($remainingMinutes = $sla->completed_at ? null : now()->diffInMinutes($sla->target_at, false))
            <tr>
                <td><a href="{{ route('requests.show', $sla->request) }}">{{ $sla->request->request_no ?? '-' }}</a></td>
                <td>{{ $sla->request->customer->name ?? '-' }}</td>
                <td>{{ ucfirst(strtolower($sla->sla_type)) }}</td>
                <td>{{ ucfirst(strtolower($sla->priority)) }}</td>
                <td>{{ $sla->responsibleUser->name ?? $sla->responsible_team ?? 'Unassigned' }}</td>
                <td>{{ $sla->target_at->format('d-m-Y h:i A') }}</td>
                <td>
                    @if($sla->completed_at)
                        <span class="text-muted">—</span>
                    @elseif($remainingMinutes >= 0)
                        <span class="text-success">{{ intdiv($remainingMinutes, 60) }}h {{ $remainingMinutes % 60 }}m</span>
                    @else
                        <span class="text-danger">-{{ intdiv(abs($remainingMinutes), 60) }}h {{ abs($remainingMinutes) % 60 }}m</span>
                    @endif
                </td>
                <td>
                    @php($badge = match($sla->status) {
                        'PENDING' => 'bg-secondary',
                        'ACTIVE' => 'bg-success',
                        'DUE_SOON' => 'bg-warning text-dark',
                        'OVERDUE' => 'bg-danger',
                        'COMPLETED_WITHIN_SLA' => 'bg-success',
                        'COMPLETED_LATE' => 'bg-orange text-dark',
                        'WAIVED' => 'bg-info text-dark',
                        default => 'bg-light text-dark border',
                    })
                    <span class="badge {{ $badge }}" style="{{ $sla->status==='COMPLETED_LATE' ? 'background:#f59e0b;' : '' }}">{{ str_replace('_',' ',$sla->status) }}</span>
                </td>
                <td><a href="{{ route('sla.show', $sla) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">Nothing here.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mb-3">{{ $slas->links() }}</div>

@can('sla.manage')
<div class="kpi-card">
    <h6 class="text-primary">Create SLA Manually</h6>
    <p class="small text-muted">Normally an SLA record is created automatically the moment Loading / Installation finishes. Use this only for an extra SLA commitment on a request (e.g. a separate Billing Submission SLA) that isn't covered by that automatic step.</p>
    <form action="{{ route('sla.store') }}" method="POST" class="row g-3">
        @csrf
        <div class="col-md-4"><label class="form-label">Request</label>
            <select class="form-select" name="request_id" required>
                <option value="">Select Request</option>
                @foreach($eligibleRequests as $r)
                    <option value="{{ $r->id }}">{{ $r->request_no }} — {{ $r->customer->name ?? '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><label class="form-label">SLA Type</label>
            <select class="form-select" name="sla_type" required>
                <option value="ACTIVATION">Activation</option>
                <option value="DELIVERY">Delivery</option>
                <option value="RESPONSE">Response</option>
                <option value="RESOLUTION">Resolution</option>
                <option value="CUSTOM">Custom</option>
            </select>
        </div>
        <div class="col-md-2"><label class="form-label">Priority</label>
            <select class="form-select" name="priority" required>
                <option value="LOW">Low</option>
                <option value="NORMAL" selected>Normal</option>
                <option value="HIGH">High</option>
                <option value="CRITICAL">Critical</option>
            </select>
        </div>
        <div class="col-md-4"><label class="form-label">SLA Start Date & Time</label><input type="datetime-local" class="form-control" name="start_at" value="{{ now()->format('Y-m-d\TH:i') }}" required></div>
        <div class="col-md-2"><label class="form-label">Duration (minutes)</label><input type="number" class="form-control" name="duration_minutes" value="480" min="1" required></div>
        <div class="col-md-3"><label class="form-label">Responsible Person</label>
            <select class="form-select" name="responsible_user_id">
                <option value="">-</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3"><label class="form-label">Responsible Team</label><input class="form-control" name="responsible_team" placeholder="NOC / Support / Billing / Operations"></div>
        <div class="col-md-2"><label class="form-label">Reminder Before (min)</label><input type="number" class="form-control" name="reminder_before_minutes" value="60" min="0"></div>
        <div class="col-md-2"><label class="form-label">Escalate After (min overdue)</label><input type="number" class="form-control" name="escalate_after_minutes" value="120" min="0"></div>
        <div class="col-12"><button class="btn btn-primary btn-sm">Create SLA</button></div>
    </form>
</div>
@endcan
@endsection
