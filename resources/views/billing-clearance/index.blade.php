@extends('layouts.app')
@section('title', 'Billing Clearance')
@section('content')
<h4 class="mb-3">Billing Clearance</h4>
<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link {{ !request('filter') ? 'active' : '' }}" href="{{ route('billing-clearance.index') }}">Pending Clearance</a></li>
    <li class="nav-item"><a class="nav-link {{ request('filter')==='cleared' ? 'active' : '' }}" href="{{ route('billing-clearance.index', ['filter'=>'cleared']) }}">Cleared</a></li>
    <li class="nav-item"><a class="nav-link {{ request('filter')==='hold' ? 'active' : '' }}" href="{{ route('billing-clearance.index', ['filter'=>'hold']) }}">Rejected / Hold</a></li>
</ul>
<div class="kpi-card p-0">
    <table class="table table-sm table-hover mb-0 align-middle">
        <thead class="table-light"><tr><th>Request No.</th><th>Customer</th><th>Outstanding</th><th>Credit Limit</th><th>Total Selling</th><th>Submitted</th><th></th></tr></thead>
        <tbody>
        @forelse($requests as $r)
            <tr>
                <td>{{ $r->request_no }}</td>
                <td>{{ $r->customer->name ?? '-' }}</td>
                <td class="{{ ($r->customer->outstanding_balance ?? 0) > 0 ? 'text-danger' : '' }}">{{ number_format($r->customer->outstanding_balance ?? 0, 2) }}</td>
                <td>{{ number_format($r->customer->credit_limit ?? 0, 2) }}</td>
                <td>{{ number_format($r->totalSellingPrice(), 2) }}</td>
                <td>{{ optional($r->submitted_at)->format('d-m-Y H:i') }}</td>
                <td><a href="{{ route('billing-clearance.show', $r) }}" class="btn btn-sm btn-primary">Review</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Nothing here.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $requests->links() }}</div>
@endsection
