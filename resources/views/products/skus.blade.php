@extends('layouts.app')
@section('title', 'Product SKUs')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Product SKU</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg"></i> Add SKU</button>
</div>
<div class="kpi-card">
    <form class="mb-3"><input class="form-control form-control-sm w-25" name="q" placeholder="Search SKU code..." value="{{ request('q') }}"></form>
    <div class="table-responsive">
    <table class="table table-sm align-middle">
        <thead><tr><th>SKU Code</th><th>Product</th><th>Billing Model</th><th>Unit</th><th>Term</th><th>Vendor Prices</th><th class="text-end">Action</th></tr></thead>
        <tbody>
        @foreach($skus as $s)
        <tr>
            <td>{{ $s->sku_code }}</td><td>{{ $s->product->name }}</td><td>{{ $s->billing_model }}</td><td>{{ $s->consumption_unit ?? '-' }}</td><td>{{ $s->term }}</td>
            <td><a href="{{ route('vendor-prices.index', ['sku_id' => $s->id]) }}">{{ $s->vendorPrices()->count() }}</a></td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editModal{{ $s->id }}"><i class="bi bi-pencil"></i></button>
                <form action="{{ route('product-skus.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    {{ $skus->links() }}
</div>

{{-- Edit modals live outside the table — a <div> is not valid inside <tbody> and browsers will otherwise hoist it out, breaking the table layout. --}}
@foreach($skus as $s)
<div class="modal fade" id="editModal{{ $s->id }}"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('product-skus.update', $s) }}" method="POST">@csrf @method('PUT')
    <div class="modal-header"><h6 class="modal-title">Edit SKU</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-2"><label class="form-label">Product</label><select class="form-select" name="product_id">@foreach($products as $p)<option value="{{ $p->id }}" {{ $s->product_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach</select></div>
        <div class="mb-2"><label class="form-label">SKU Code</label><input class="form-control" name="sku_code" value="{{ $s->sku_code }}" required></div>
        <div class="mb-2"><label class="form-label">Billing Model</label>
            <select class="form-select" name="billing_model">
                @foreach(['SEAT_BASED','CONSUMPTION','ONE_TIME'] as $bm)<option value="{{ $bm }}" {{ $s->billing_model === $bm ? 'selected' : '' }}>{{ $bm }}</option>@endforeach
            </select>
        </div>
        <div class="mb-2"><label class="form-label">Consumption Unit</label><input class="form-control" name="consumption_unit" value="{{ $s->consumption_unit }}"></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary btn-sm">Save</button></div>
    </form>
</div></div></div>
@endforeach

<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('product-skus.store') }}" method="POST">@csrf
    <div class="modal-header"><h6 class="modal-title">Add SKU</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-2"><label class="form-label">Product</label><select class="form-select" name="product_id" required>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
        <div class="mb-2"><label class="form-label">SKU Code</label><input class="form-control" name="sku_code" required></div>
        <div class="mb-2"><label class="form-label">Billing Model</label>
            <select class="form-select" name="billing_model"><option value="SEAT_BASED">SEAT_BASED</option><option value="CONSUMPTION">CONSUMPTION</option><option value="ONE_TIME">ONE_TIME</option></select>
        </div>
        <div class="mb-2"><label class="form-label">Consumption Unit</label><input class="form-control" name="consumption_unit"></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary btn-sm">Save</button></div>
    </form>
</div></div></div>
@endsection
