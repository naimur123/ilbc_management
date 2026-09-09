@extends('layouts.app')
@section('title', 'Audit Logs')
@section('content')
<h4 class="mb-3">Audit Logs</h4>
<form class="kpi-card mb-3 row g-2" method="GET">
    <div class="col-md-3">
        <select class="form-select form-select-sm" name="module">
            <option value="">All Modules</option>
            @foreach($modules as $m)<option value="{{ $m }}" {{ request('module')===$m?'selected':'' }}>{{ $m }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-3"><input type="date" class="form-control form-control-sm" name="from" value="{{ request('from') }}"></div>
    <div class="col-md-3"><input type="date" class="form-control form-control-sm" name="to" value="{{ request('to') }}"></div>
    <div class="col-md-2"><button class="btn btn-sm btn-outline-secondary w-100">Filter</button></div>
</form>
<div class="kpi-card p-0">
    <table class="table table-sm table-hover mb-0 align-middle">
        <thead class="table-light"><tr><th>Date/Time</th><th>User</th><th>Role</th><th>Module</th><th>Action</th><th>Old → New</th><th>IP</th></tr></thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('d-m-Y H:i:s') }}</td>
                <td>{{ $log->user->name ?? 'System' }}</td>
                <td>{{ $log->role_name ?? '-' }}</td>
                <td>{{ $log->module }}</td>
                <td>{{ $log->action }}</td>
                <td class="small">{{ $log->old_value }} @if($log->old_value && $log->new_value) → @endif {{ $log->new_value }}</td>
                <td class="small text-muted">{{ $log->ip_address }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No log entries.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $logs->links() }}</div>
@endsection
