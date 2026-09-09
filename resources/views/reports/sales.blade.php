@extends('layouts.app')
@section('title', 'Sales Report')
@section('content')
<h4 class="mb-3">Sales Report</h4>
<form class="kpi-card mb-3 row g-2" method="GET">
    <div class="col-md-3"><input type="date" class="form-control form-control-sm" name="from" value="{{ request('from') }}"></div>
    <div class="col-md-3"><input type="date" class="form-control form-control-sm" name="to" value="{{ request('to') }}"></div>
    <div class="col-md-2"><button class="btn btn-sm btn-outline-secondary w-100">Filter</button></div>
    <div class="col-md-2"><button class="btn btn-sm btn-outline-primary w-100" onclick="window.print()" type="button"><i class="bi bi-printer"></i> Print</button></div>
</form>
<div class="kpi-card mb-3"><strong>Total Sales:</strong> {{ config('ilbc.currency_symbol') }} {{ number_format($totalSales,2) }} across {{ $requests->count() }} requests</div>
<div class="kpi-card p-0">
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Request No.</th><th>Date</th><th>Customer</th><th>Salesperson</th><th>Total Selling</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($requests as $r)
            <tr><td>{{ $r->request_no }}</td><td>{{ $r->created_at->format('d-m-Y') }}</td><td>{{ $r->customer->name ?? '-' }}</td><td>{{ $r->salesperson->name ?? '-' }}</td><td>{{ number_format($r->totalSellingPrice(),2) }}</td><td>{{ str_replace('_',' ',$r->status) }}</td></tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
