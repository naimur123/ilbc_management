@extends('layouts.app')
@section('title', 'Billing & Invoice')
@section('content')
<h4 class="mb-3">Billing & Invoice</h4>
<ul class="nav nav-pills mb-3">
    <li class="nav-item"><a class="nav-link {{ !request('filter') ? 'active' : '' }}" href="{{ route('billing-invoice.index') }}">Pending Invoice</a></li>
    <li class="nav-item"><a class="nav-link {{ request('filter')==='generated' ? 'active' : '' }}" href="{{ route('billing-invoice.index', ['filter'=>'generated']) }}">Invoice Generated</a></li>
    <li class="nav-item"><a class="nav-link {{ request('filter')==='sent' ? 'active' : '' }}" href="{{ route('billing-invoice.index', ['filter'=>'sent']) }}">Invoice Sent</a></li>
    <li class="nav-item"><a class="nav-link {{ request('filter')==='done' ? 'active' : '' }}" href="{{ route('billing-invoice.index', ['filter'=>'done']) }}">Billing Done</a></li>
</ul>
<div class="kpi-card p-0">
    <table class="table table-sm table-hover mb-0 align-middle">
        <thead class="table-light"><tr><th>Request No.</th><th>Customer</th><th>Total Selling</th><th>Invoice No.</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($requests as $r)
            <tr>
                <td>{{ $r->request_no }}</td><td>{{ $r->customer->name ?? '-' }}</td>
                <td>{{ number_format($r->totalSellingPrice(),2) }}</td>
                <td>{{ $r->invoices->last()->invoice_no ?? '-' }}</td>
                <td><span class="badge bg-light text-dark border">{{ str_replace('_',' ',$r->status) }}</span></td>
                <td><a href="{{ route('billing-invoice.show', $r) }}" class="btn btn-sm btn-primary">Open</a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Nothing here.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $requests->links() }}</div>
@endsection
