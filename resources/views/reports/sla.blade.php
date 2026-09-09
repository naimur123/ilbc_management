@extends('layouts.app')
@section('title', 'SLA / Aging Report')
@section('content')
<h4 class="mb-3">SLA / Aging Report</h4>
<div class="kpi-card p-0">
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Request No.</th><th>Customer</th><th>Current Stage</th><th>Hours Pending</th><th>SLA Status</th></tr></thead>
        <tbody>
        @foreach($requests as $r)
            <tr>
                <td>{{ $r->request_no }}</td><td>{{ $r->customer->name ?? '-' }}</td><td>{{ str_replace('_',' ',$r->current_stage) }}</td>
                <td>{{ $r->hours_pending }}h</td>
                <td>
                    @if($r->sla_status === 'OVERDUE')<span class="status-overdue fw-bold">Overdue</span>
                    @elseif($r->sla_status === 'DUE_SOON')<span class="status-due-soon fw-bold">Due Soon</span>
                    @else<span class="status-on-time fw-bold">On Time</span>@endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
