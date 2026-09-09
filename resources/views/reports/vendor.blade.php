@extends('layouts.app')
@section('title', 'Vendor Report')
@section('content')
<h4 class="mb-3">Vendor Report</h4>
<div class="kpi-card p-0">
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Vendor</th><th>Type</th><th>SKUs Priced</th><th>Times Selected</th><th>Lowest-Cost Wins</th><th>Total Purchase Value</th></tr></thead>
        <tbody>
        @foreach($vendors as $v)
            <tr>
                <td>{{ $v->name }}</td><td>{{ $v->vendor_type }}</td><td>{{ $v->product_prices_count }}</td>
                <td>{{ $v->selection_count }}</td><td>{{ $v->lowest_cost_hits }}</td><td>{{ number_format($v->total_purchase,2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
