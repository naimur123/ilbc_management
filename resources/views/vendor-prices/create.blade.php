@extends('layouts.app')
@section('title', 'Add Vendor Price')
@section('content')
<h4 class="mb-3">Add / Update Vendor Product Price</h4>
<p class="text-muted small">Saving here never overwrites the current price — it closes off the old one and keeps it in Price History (Section 12).</p>
@if($preselectedSkuId)
    <div class="alert alert-info py-2 small">
        <i class="bi bi-info-circle"></i> No vendor has a price configured yet for
        <strong>{{ optional($skus->firstWhere('id', $preselectedSkuId))->sku_code }}</strong> — add one below for at least one active vendor so it can be compared and selected on the Reviewer's Vendor Comparison screen.
    </div>
@endif
<form action="{{ route('vendor-prices.store') }}" method="POST" class="kpi-card">
    @csrf
    @if($returnTo)<input type="hidden" name="return_to" value="{{ $returnTo }}">@endif
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Vendor *</label>
            <select class="form-select" name="vendor_id" required><option value="">-</option>
            @foreach($vendors as $v)<option value="{{ $v->id }}">{{ $v->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4"><label class="form-label">Product SKU *</label>
            <select class="form-select" name="product_sku_id" required {{ $preselectedSkuId ? 'data-preselected='.$preselectedSkuId : '' }}><option value="">-</option>
            @foreach($skus as $s)<option value="{{ $s->id }}" {{ $preselectedSkuId == $s->id ? 'selected' : '' }}>{{ $s->product->name }} ({{ $s->sku_code }})</option>@endforeach
            </select>
        </div>
        <div class="col-md-4"><label class="form-label">Currency *</label>
            <select class="form-select" name="currency_id" required>
            @foreach($currencies as $c)<option value="{{ $c->id }}" {{ $c->is_base ? 'selected' : '' }}>{{ $c->code }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3"><label class="form-label">Unit Purchase Price *</label><input type="number" step="0.01" class="form-control" name="unit_purchase_price" required></div>
        <div class="col-md-3"><label class="form-label">VAT %</label><input type="number" step="0.001" class="form-control" name="vat_percent" value="0"></div>
        <div class="col-md-3"><label class="form-label">TAX %</label><input type="number" step="0.001" class="form-control" name="tax_percent" value="0"></div>
        <div class="col-md-3"><label class="form-label">Minimum Quantity</label><input type="number" class="form-control" name="minimum_quantity" value="1"></div>
        <div class="col-md-4"><label class="form-label">Other Cost</label><input type="number" step="0.01" class="form-control" name="other_cost" value="0"></div>
        <div class="col-md-4"><label class="form-label">Handling Cost</label><input type="number" step="0.01" class="form-control" name="handling_cost" value="0"></div>
        <div class="col-md-4"><label class="form-label">Delivery Cost</label><input type="number" step="0.01" class="form-control" name="delivery_cost" value="0"></div>
        <div class="col-md-4"><label class="form-label">Effective From *</label><input type="date" class="form-control" name="effective_from" value="{{ now()->toDateString() }}" required></div>
        <div class="col-md-4"><label class="form-label">Price Type *</label>
            <select class="form-select" name="price_type">
                @foreach(['LIST','PARTNER_DISCOUNTED','PROMO','CUSTOM_QUOTE'] as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
            </select>
        </div>
        <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks"></textarea></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Save Price</button> <a href="{{ $returnTo ?: route('vendor-prices.index') }}" class="btn btn-light">Cancel</a></div>
</form>
@endsection
