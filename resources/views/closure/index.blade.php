@extends('layouts.app')
@section('title', 'Closure')
@section('content')
<h4 class="mb-3">Closure</h4>
<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link {{ !request('filter') ? 'active' : '' }}" href="{{ route('closure.index') }}">Ready for Closure</a></li>
    <li class="nav-item"><a class="nav-link {{ request('filter')==='closed' ? 'active' : '' }}" href="{{ route('closure.index', ['filter'=>'closed']) }}">Closed Requests</a></li>
    <li class="nav-item"><a class="nav-link {{ request('filter')==='reopened' ? 'active' : '' }}" href="{{ route('closure.index', ['filter'=>'reopened']) }}">Reopened Requests</a></li>
</ul>
<div class="kpi-card p-0">
    <table class="table table-sm table-hover mb-0 align-middle">
        <thead class="table-light"><tr><th>Request No.</th><th>Customer</th><th>Selling Amount</th><th>Vendor Cost</th><th>Billing</th><th>Collection</th><th>Audit</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($requests as $r)
            @php($billingDone = $r->billingRecord?->billing_done_at)
            @php($collectionStatus = $r->billingRecord?->collection_status)
            @php($auditApproved = $r->items->isNotEmpty() && $r->items->every(fn($i) => $i->auditRecord?->decision === 'APPROVE'))
            <tr>
                <td>{{ $r->request_no }}</td><td>{{ $r->customer->name ?? '-' }}</td>
                <td>{{ number_format($r->totalSellingPrice(),2) }}</td>
                <td>{{ number_format($r->items->sum(fn($i)=>$i->vendorSelection->final_landed_cost ?? 0),2) }}</td>
                <td>{!! $billingDone ? '<span class="badge bg-success">Done</span>' : '<span class="badge bg-secondary">Pending</span>' !!}</td>
                <td>
                    @if($collectionStatus === 'RECEIVED')<span class="badge bg-success">Received</span>
                    @elseif($collectionStatus === 'PARTIAL')<span class="badge bg-warning text-dark">Partial</span>
                    @else<span class="badge bg-secondary">Pending</span>@endif
                </td>
                <td>{!! $auditApproved ? '<span class="badge bg-success">Approved</span>' : '<span class="badge bg-secondary">Pending</span>' !!}</td>
                <td><span class="badge bg-light text-dark border">{{ str_replace('_',' ',$r->status) }}</span></td>
                <td><a href="{{ route('closure.show', $r) }}" class="btn btn-sm btn-primary">Review</a></td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">Nothing here.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $requests->links() }}</div>
@endsection
