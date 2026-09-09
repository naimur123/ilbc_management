@extends('layouts.app')
@section('title', 'Audit')
@section('content')
<h4 class="mb-3">Audit</h4>
<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link {{ !request('filter') ? 'active' : '' }}" href="{{ route('audit.index') }}">Audit Pending</a></li>
    <li class="nav-item"><a class="nav-link {{ request('filter')==='approved' ? 'active' : '' }}" href="{{ route('audit.index', ['filter'=>'approved']) }}">Approved</a></li>
    <li class="nav-item"><a class="nav-link {{ request('filter')==='returned' ? 'active' : '' }}" href="{{ route('audit.index', ['filter'=>'returned']) }}">Returned for Correction</a></li>
    <li class="nav-item"><a class="nav-link {{ request('filter')==='hold' ? 'active' : '' }}" href="{{ route('audit.index', ['filter'=>'hold']) }}">On Hold</a></li>
</ul>
<div class="kpi-card p-0">
    <table class="table table-sm table-hover mb-0 align-middle">
        <thead class="table-light"><tr><th>Request No.</th><th>Customer</th><th>Product / SKU</th><th>Qty</th><th>Vendor</th><th>Loaded By</th><th>Loading Date</th><th>Variance</th><th></th></tr></thead>
        <tbody>
        @forelse($items as $item)
            <tr>
                <td>{{ $item->request->request_no ?? '-' }}</td>
                <td>{{ $item->request->customer->name ?? '-' }}</td>
                <td>{{ $item->product->name ?? '-' }} <small class="text-muted">{{ $item->sku->sku_code ?? '' }}</small></td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->vendorSelection->vendor->name ?? '-' }}</td>
                <td>{{ $item->loadingRecord?->loadedBy->name ?? '-' }}</td>
                <td>{{ optional($item->loadingRecord?->loading_date)->format('d-m-Y') ?? '-' }}</td>
                <td>{!! $item->auditRecord?->has_variance ? '<span class="badge bg-danger">Variance</span>' : '<span class="badge bg-success">OK</span>' !!}</td>
                <td><a href="{{ route('audit.show', $item) }}" class="btn btn-sm btn-primary">Review</a></td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">Nothing here.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
