@extends('layouts.app')
@section('title', 'Profit Report')
@section('content')
<h4 class="mb-3">Profitability Report — {{ $month->format('M-Y') }}</h4>
<form class="kpi-card mb-3 row g-2" method="GET">
    <div class="col-md-3"><input type="month" class="form-control form-control-sm" name="month" value="{{ $month->format('Y-m') }}"></div>
    <div class="col-md-2"><button class="btn btn-sm btn-outline-secondary w-100">View</button></div>
</form>
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="kpi-card"><div class="text-muted small">Sales</div><div class="kpi-value">{{ number_format($sales,2) }}</div></div></div>
    <div class="col-md-3"><div class="kpi-card"><div class="text-muted small">Purchase/Landed Cost</div><div class="kpi-value">{{ number_format($cost,2) }}</div></div></div>
    <div class="col-md-3"><div class="kpi-card"><div class="text-muted small">Gross Profit</div><div class="kpi-value {{ $profit>=0?'text-success':'text-danger' }}">{{ number_format($profit,2) }}</div></div></div>
    <div class="col-md-3"><div class="kpi-card"><div class="text-muted small">Gross Margin %</div><div class="kpi-value">{{ $margin }}%</div></div></div>
</div>
<div class="kpi-card p-0">
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Request No.</th><th>Selling</th><th>Landed Cost</th><th>Profit</th></tr></thead>
        <tbody>
        @foreach($requests as $r)
            @php($rc = $r->items->sum(fn($i)=>$i->vendorSelection->final_landed_cost ?? 0))
            <tr><td>{{ $r->request_no }}</td><td>{{ number_format($r->totalSellingPrice(),2) }}</td><td>{{ number_format($rc,2) }}</td><td>{{ number_format($r->totalSellingPrice()-$rc,2) }}</td></tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
