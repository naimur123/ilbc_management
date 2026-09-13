@extends('layouts.app')
@section('title', 'Vendor Comparison')
@section('content')
<h4 class="mb-3">Vendor Comparison — {{ $item->product->name ?? '' }} ({{ $item->sku->sku_code ?? '' }})</h4>
<p class="text-muted">Request {{ $request->request_no }} — Quantity {{ $item->quantity }} — Total Selling {{ number_format($item->total_selling_price,2) }}</p>

<div class="kpi-card p-0">
    <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead class="table-light">
            <tr><th>Vendor</th><th>Unit Cost</th><th>Qty</th><th>Base Cost</th><th>VAT/Tax</th><th>Other Cost</th><th>Final Cost</th><th>Profit</th><th>Margin %</th><th></th></tr>
        </thead>
        <tbody>
        @forelse($rows as $row)
            <tr class="{{ $row['is_best_price'] ? 'table-success' : '' }}">
                <td>{{ $row['vendor']->name }} @if($row['is_best_price'])<span class="badge bg-success">Best Price</span>@endif</td>
                <td>{{ number_format($row['unit_cost'],2) }}</td>
                <td>{{ $row['quantity'] }}</td>
                <td>{{ number_format($row['base_cost'],2) }}</td>
                <td>{{ number_format($row['vat_amount'] + $row['tax_amount'],2) }}</td>
                <td>{{ number_format($row['other_cost'],2) }}</td>
                <td class="fw-bold">{{ number_format($row['final_landed_cost'],2) }}</td>
                <td class="{{ $row['gross_profit']>=0?'text-success':'text-danger' }}">{{ number_format($row['gross_profit'],2) }}</td>
                <td>{{ number_format($row['gross_margin_percent'],2) }}%</td>
                <td>
                    @can('vendor.select')
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#selectModal{{ $row['vendor_product_price_id'] }}">Select</button>
                    @endcan
                </td>
            </tr>
        @empty
            <tr><td colspan="10" class="text-center text-muted py-4">
                No vendor prices configured for this SKU yet — this is not an error, it just means Vendor Management → Vendor Product Price has no rate saved for <strong>{{ $item->sku->sku_code ?? 'this SKU' }}</strong> from any active vendor.
            </td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>

@if(empty($rows))
    @can('vendor.price_manage')
    <div class="alert alert-warning mt-3">
        <i class="bi bi-exclamation-triangle"></i> A vendor price must exist for this SKU before it can be compared and selected here.
        <a href="{{ route('vendor-prices.create', ['sku_id' => $item->sku->id, 'return_to' => url()->current()]) }}" class="btn btn-sm btn-primary ms-2">Add Vendor Price for {{ $item->sku->sku_code ?? 'this SKU' }}</a>
    </div>
    @else
    <div class="alert alert-warning mt-3">
        <i class="bi bi-exclamation-triangle"></i> A vendor price must exist for this SKU before it can be compared and selected here. Ask an Admin/Vendor Manager (permission: Vendor → Price Manage) to add one under Vendor Management → Vendor Product Price for <strong>{{ $item->sku->sku_code ?? 'this SKU' }}</strong>.
    </div>
    @endcan
@endif

<a href="{{ route('reviewer.show', $request) }}" class="btn btn-light mt-3">Back to Review</a>

@foreach($rows as $row)
<div class="modal fade" id="selectModal{{ $row['vendor_product_price_id'] }}"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('requests.items.vendor-selection', [$request, $item]) }}" method="POST">
        @csrf
        <input type="hidden" name="vendor_product_price_id" value="{{ $row['vendor_product_price_id'] }}">
        <div class="modal-header"><h6 class="modal-title">Select {{ $row['vendor']->name }}</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p>Final Landed Cost: <strong>{{ number_format($row['final_landed_cost'],2) }}</strong> — Margin: <strong>{{ number_format($row['gross_margin_percent'],2) }}%</strong></p>
            @if(!$row['is_best_price'])
                <div class="alert alert-warning small">This is not the lowest-cost vendor. A reason is required (Rule 8).</div>
                <label class="form-label">Reason *</label>
                <textarea class="form-control" name="override_reason" required></textarea>
            @endif
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Confirm Selection</button></div>
    </form>
</div></div></div>
@endforeach
@endsection
