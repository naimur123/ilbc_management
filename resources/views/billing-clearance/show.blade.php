@extends('layouts.app')
@section('title', 'Billing Clearance - '.$request->request_no)
@section('content')
<h4 class="mb-3">Billing Clearance — {{ $request->request_no }}</h4>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="kpi-card h-100">
            <h6 class="text-primary">Customer</h6>
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Name</th><td>{{ $request->customer->name }}</td></tr>
                <tr><th class="text-muted">Outstanding Balance</th><td class="{{ $request->customer->outstanding_balance > 0 ? 'text-danger fw-bold' : '' }}">{{ config('ilbc.currency_symbol') }} {{ number_format($request->customer->outstanding_balance, 2) }}</td></tr>
                <tr><th class="text-muted">Credit Limit</th><td>{{ config('ilbc.currency_symbol') }} {{ number_format($request->customer->credit_limit, 2) }}</td></tr>
                <tr><th class="text-muted">Payment Terms</th><td>{{ $request->salesEntry->paymentTerm->name ?? '-' }}</td></tr>
                <tr><th class="text-muted">Advance % / Credit Days</th><td>{{ $request->salesEntry->advance_percent ?? 0 }}% / {{ $request->salesEntry->credit_days ?? 0 }} days</td></tr>
                @if($request->customer->isOverCreditLimit())
                <tr><td colspan="2"><span class="badge bg-danger">Outstanding exceeds Credit Limit — Special Approval Required</span></td></tr>
                @endif
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="kpi-card h-100">
            <h6 class="text-primary">Order Summary</h6>
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Work Order</th><td>{{ $request->work_order_no ?: '-' }}</td></tr>
                <tr><th class="text-muted">PO Number</th><td>{{ $request->po_number ?: '-' }}</td></tr>
                <tr><th class="text-muted">Total Selling Price</th><td class="fw-bold">{{ config('ilbc.currency_symbol') }} {{ number_format($request->totalSellingPrice(), 2) }}</td></tr>
                <tr><th class="text-muted">Items</th><td>{{ $request->items->count() }}</td></tr>
            </table>
        </div>
    </div>
</div>

<div class="kpi-card mb-3 p-0">
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Product</th><th>SKU</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
        <tbody>
        @foreach($request->items as $item)
            <tr><td>{{ $item->product->name ?? '-' }}</td><td>{{ $item->sku->sku_code ?? '-' }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_selling_price,2) }}</td><td>{{ number_format($item->total_selling_price,2) }}</td></tr>
        @endforeach
        </tbody>
    </table>
</div>

@can('billing.clearance.approve')
<div class="kpi-card">
    <h6 class="text-primary">Decision</h6>
    <form action="{{ route('billing-clearance.decide', $request) }}" method="POST" class="row g-3">
        @csrf
        <div class="col-md-4 form-check">
            <input type="checkbox" class="form-check-input" name="advance_payment_required" value="1" id="advReq">
            <label class="form-check-label" for="advReq">Advance Payment Required</label>
        </div>
        <div class="col-md-4 form-check">
            <input type="checkbox" class="form-check-input" name="security_deposit_required" value="1" id="secReq">
            <label class="form-check-label" for="secReq">Security Deposit Required</label>
        </div>
        <div class="col-12"><label class="form-label">Remarks (mandatory for Hold/Reject)</label><textarea class="form-control" name="remarks"></textarea></div>
        <div class="col-12">
            <button class="btn btn-success" name="decision" value="GREEN_SIGNAL"><i class="bi bi-check-circle"></i> Green Signal</button>
            <button class="btn btn-warning" name="decision" value="HOLD"><i class="bi bi-pause-circle"></i> Hold</button>
            <button class="btn btn-danger" name="decision" value="REJECT"><i class="bi bi-x-circle"></i> Reject</button>
        </div>
    </form>
</div>
@endcan
@endsection
