@extends('layouts.app')
@section('title', 'Requests')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Requests</h4>
    @can('request.create')
    <a href="{{ route('requests.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Create New Request</a>
    @endcan
</div>

<div class="kpi-card mb-3">
    <form class="row g-2" method="GET">
        <div class="col-md-3">
            <input class="form-control form-control-sm" type="search" name="q" value="{{ request('q') }}" placeholder="Request No. / Customer">
        </div>
        <div class="col-md-3">
            <select class="form-select form-select-sm" name="status">
                <option value="">All Statuses</option>
                @foreach(\App\Models\Request::query()->distinct()->pluck('status') as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ str_replace('_',' ',$s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 form-check pt-2">
            <input type="checkbox" class="form-check-input" name="mine" value="1" id="mineOnly" {{ request('mine') ? 'checked' : '' }}>
            <label class="form-check-label" for="mineOnly">My Requests Only</label>
        </div>
        <div class="col-md-2"><button class="btn btn-outline-secondary btn-sm w-100">Filter</button></div>
    </form>
</div>

<div class="kpi-card p-0">
    <table class="table table-sm table-hover mb-0 align-middle">
        <thead class="table-light">
            <tr>
                <th>Request No.</th><th>Date</th><th>Customer</th><th>Salesperson</th><th>Items</th><th>Total Selling</th><th>Stage</th><th>Status</th><th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($requests as $r)
            <tr>
                <td><a href="{{ route('requests.show', $r) }}">{{ $r->request_no }}</a></td>
                <td>{{ $r->created_at->format('d-m-Y') }}</td>
                <td>{{ $r->customer->name ?? '-' }}</td>
                <td>{{ $r->salesperson->name ?? '-' }}</td>
                <td>{{ $r->items()->count() }}</td>
                <td>{{ config('ilbc.currency_symbol') }} {{ number_format($r->totalSellingPrice(), 2) }}</td>
                <td><span class="badge bg-light text-dark border">{{ str_replace('_', ' ', $r->current_stage) }}</span></td>
                <td><span class="badge badge-stage bg-primary-subtle text-primary-emphasis">{{ str_replace('_',' ',$r->status) }}</span></td>
                <td><a href="{{ route('requests.show', $r) }}" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">No requests found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $requests->links() }}</div>
@endsection
