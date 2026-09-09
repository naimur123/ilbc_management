@extends('layouts.app')
@section('title', 'Vendor Performance')
@section('content')
<h4 class="mb-3">Vendor Performance</h4>
<div class="kpi-card">
    <table class="table table-sm align-middle">
        <thead><tr><th>Vendor</th><th>Priced SKUs</th><th>Times Selected</th><th>Times Was Lowest Cost</th><th>Total Purchase Value</th></tr></thead>
        <tbody>
        @foreach($vendors as $v)
        <tr>
            <td>{{ $v->name }}</td><td>{{ $v->sku_count }}</td><td>{{ $v->selection_count }}</td>
            <td>{{ $v->lowest_cost_wins }} ({{ $v->selection_count ? round($v->lowest_cost_wins / $v->selection_count * 100) : 0 }}%)</td>
            <td>{{ number_format($v->total_purchase_value, 2) }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
