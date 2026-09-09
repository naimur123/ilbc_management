@extends('layouts.app')
@section('title', 'Reviewer Approval')
@section('content')
<h4 class="mb-3">Reviewer Approval</h4>
<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link {{ !request('filter') ? 'active' : '' }}" href="{{ route('reviewer.index') }}">Pending Review</a></li>
    <li class="nav-item"><a class="nav-link {{ request('filter')==='approved' ? 'active' : '' }}" href="{{ route('reviewer.index', ['filter'=>'approved']) }}">Approved</a></li>
    <li class="nav-item"><a class="nav-link {{ request('filter')==='returned' ? 'active' : '' }}" href="{{ route('reviewer.index', ['filter'=>'returned']) }}">Returned / Rejected</a></li>
</ul>
<div class="kpi-card p-0">
    <table class="table table-sm table-hover mb-0 align-middle">
        <thead class="table-light"><tr><th>Request No.</th><th>Customer</th><th>Salesperson</th><th>Total Selling</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($requests as $r)
            <tr>
                <td>{{ $r->request_no }}</td><td>{{ $r->customer->name ?? '-' }}</td><td>{{ $r->salesperson->name ?? '-' }}</td>
                <td>{{ number_format($r->totalSellingPrice(), 2) }}</td>
                <td><span class="badge bg-purple text-white" style="background:#7c3aed;">{{ str_replace('_',' ',$r->status) }}</span></td>
                <td><a href="{{ route('reviewer.show', $r) }}" class="btn btn-sm btn-primary">Review</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Nothing here.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $requests->links() }}</div>
@endsection
