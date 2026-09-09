@extends('layouts.app')
@section('title', 'Search Results')
@section('content')
<h4 class="mb-3">Search Results for "{{ $q }}"</h4>

<div class="kpi-card mb-3 p-0">
    <div class="p-3 pb-0"><h6 class="text-primary">Requests</h6></div>
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Request No.</th><th>Customer</th><th>Work Order</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($requests as $r)
            <tr><td>{{ $r->request_no }}</td><td>{{ $r->customer->name ?? '-' }}</td><td>{{ $r->work_order_no ?: '-' }}</td><td>{{ str_replace('_',' ',$r->status) }}</td><td><a href="{{ route('requests.show', $r) }}" class="btn btn-sm btn-light">View</a></td></tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-3">No matching requests.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="kpi-card p-0">
    <div class="p-3 pb-0"><h6 class="text-primary">Invoices</h6></div>
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Invoice No.</th><th>Request</th><th>Amount</th><th></th></tr></thead>
        <tbody>
        @forelse($invoices as $inv)
            <tr><td>{{ $inv->invoice_no }}</td><td>{{ $inv->request->request_no ?? '-' }}</td><td>{{ number_format($inv->total_amount,2) }}</td><td><a href="{{ route('billing-invoice.show', $inv->request) }}" class="btn btn-sm btn-light">View</a></td></tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-3">No matching invoices.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
