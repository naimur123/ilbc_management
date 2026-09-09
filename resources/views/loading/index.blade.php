@extends('layouts.app')
@section('title', 'Loading / Installation')
@section('content')
<h4 class="mb-3">Loading / Installation</h4>
<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link {{ !$filter ? 'active' : '' }}" href="{{ route('loading.index') }}">Pending Loading</a></li>
    <li class="nav-item"><a class="nav-link {{ $filter==='mine' ? 'active' : '' }}" href="{{ route('loading.index', ['filter'=>'mine']) }}">My Loading</a></li>
    <li class="nav-item"><a class="nav-link {{ $filter==='completed' ? 'active' : '' }}" href="{{ route('loading.index', ['filter'=>'completed']) }}">Completed Loading</a></li>
</ul>
<div class="kpi-card p-0">
    <div class="table-responsive">
    <table class="table table-sm table-hover mb-0 align-middle">
        <thead class="table-light"><tr><th>Request No.</th><th>Customer</th><th>Product / SKU</th><th>Qty</th><th>Vendor</th><th>Approval Date</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($requests as $r)
            @php
                $firstItem = $r->items->first();
                $extraItems = $r->items->count() - 1;
                $vendorNames = $r->items->pluck('vendorSelection.vendor.name')->filter()->unique()->values();
                $allCompleted = $r->items->isNotEmpty() && $r->items->every(fn($i) => $i->loadingRecord?->status === 'COMPLETED');
                $anyInProgress = $r->items->contains(fn($i) => $i->loadingRecord && $i->loadingRecord->status !== 'COMPLETED');
            @endphp
            <tr>
                <td>{{ $r->request_no }}</td>
                <td>{{ $r->customer->name ?? '-' }}</td>
                <td>
                    {{ $firstItem?->product->name ?? '-' }} <small class="text-muted">{{ $firstItem?->sku->sku_code ?? '' }}</small>
                    @if($extraItems > 0)<span class="badge bg-light text-dark border">+{{ $extraItems }} more</span>@endif
                </td>
                <td>{{ $r->items->sum('quantity') }}</td>
                <td>{{ $vendorNames->isNotEmpty() ? $vendorNames->join(', ') : '-' }}</td>
                <td>{{ optional($r->reviewerApproval?->decided_at)->format('d-m-Y') ?? '-' }}</td>
                <td>
                    @if($allCompleted)<span class="badge bg-success">Completed</span>
                    @elseif($anyInProgress)<span class="badge bg-warning text-dark">In Progress</span>
                    @else<span class="badge bg-secondary">Pending Loading</span>@endif
                </td>
                <td><a href="{{ route('loading.review', $r) }}" class="btn btn-sm btn-primary">Review</a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Nothing here.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
<div class="mt-3">{{ $requests->links() }}</div>
@endsection
