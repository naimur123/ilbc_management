@extends('layouts.app')
@section('title', 'Vendor Product Price')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Vendor Product Price</h4>
    @can('vendor.price_manage')<a href="{{ route('vendor-prices.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add / Update Price</a>@endcan
</div>
<div class="kpi-card">
    <form class="mb-3 row g-2" method="GET">
        <div class="col-auto"><select class="form-select form-select-sm" name="vendor_id" onchange="this.form.submit()">
            <option value="">All Vendors</option>
            @foreach($vendors as $v)<option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>@endforeach
        </select></div>
    </form>
    <div class="table-responsive">
    <table class="table table-sm align-middle">
        <thead><tr><th>Vendor</th><th>Product</th><th>SKU</th><th>Unit Cost</th><th>VAT%</th><th>TAX%</th><th>Other/Handling/Delivery</th><th>Effective From</th><th>Type</th></tr></thead>
        <tbody>
        @forelse($prices as $p)
        <tr>
            <td>{{ $p->vendor->name }}</td><td>{{ $p->sku->product->name }}</td><td>{{ $p->sku->sku_code }}</td>
            <td>{{ number_format($p->unit_purchase_price, 2) }} {{ $p->currency->code }}</td>
            <td>{{ $p->vat_percent }}</td><td>{{ $p->tax_percent }}</td>
            <td>{{ number_format($p->other_cost + $p->handling_cost + $p->delivery_cost, 2) }}</td>
            <td>{{ $p->effective_from->format('d-m-Y') }}</td>
            <td><span class="badge bg-light text-dark border">{{ $p->price_type }}</span></td>
        </tr>
        @empty
        <tr><td colspan="9" class="text-center text-muted py-3">No vendor prices yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    {{ $prices->links() }}
</div>
@endsection
