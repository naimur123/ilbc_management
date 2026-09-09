@extends('layouts.app')
@section('title', 'Billing & Invoice - '.$request->request_no)
@section('content')
@php($invoice = $request->invoices->last())
@php($br = $request->billingRecord)
<h4 class="mb-3">Billing & Invoice — {{ $request->request_no }}</h4>

<div class="kpi-card mb-3">
    <h6 class="text-primary">Order Summary</h6>
    <table class="table table-sm mb-0">
        <tr><th class="text-muted">Customer</th><td>{{ $request->customer->name }}</td></tr>
        <tr><th class="text-muted">Total Selling Price</th><td class="fw-bold">{{ config('ilbc.currency_symbol') }} {{ number_format($request->totalSellingPrice(),2) }}</td></tr>
        <tr><th class="text-muted">Invoice Generated</th><td>{{ $br?->invoice_generated_at ? $br->invoice_generated_at->format('d-m-Y H:i') : 'Not yet' }}</td></tr>
        <tr><th class="text-muted">Invoice Sent</th><td>{{ $br?->invoice_sent_at ? $br->invoice_sent_at->format('d-m-Y H:i') : 'Not yet' }}</td></tr>
        <tr><th class="text-muted">Billing Done</th><td>{{ $br?->billing_done_at ? $br->billing_done_at->format('d-m-Y H:i') : 'Not yet' }}</td></tr>
    </table>
</div>

@if($invoice)
<div class="kpi-card mb-3">
    <h6 class="text-primary">Invoice {{ $invoice->invoice_no }}</h6>
    <table class="table table-sm mb-0">
        <tr><th class="text-muted">Invoice Date</th><td>{{ $invoice->invoice_date->format('d-m-Y') }}</td></tr>
        <tr><th class="text-muted">Billing Month</th><td>{{ $invoice->billing_month }}</td></tr>
        <tr><th class="text-muted">Amount</th><td>{{ number_format($invoice->invoice_amount,2) }}</td></tr>
        <tr><th class="text-muted">VAT / Tax / Other</th><td>{{ number_format($invoice->vat_amount,2) }} / {{ number_format($invoice->tax_amount,2) }} / {{ number_format($invoice->other_charges,2) }}</td></tr>
        <tr><th class="text-muted">Total</th><td class="fw-bold">{{ number_format($invoice->total_amount,2) }}</td></tr>
        <tr><th class="text-muted">Due Date</th><td>{{ optional($invoice->due_date)->format('d-m-Y') ?? '-' }}</td></tr>
    </table>
    <a href="{{ route('billing-invoice.pdf', $invoice) }}" class="btn btn-sm btn-outline-secondary mt-2"><i class="bi bi-file-earmark-pdf"></i> Download PDF</a>
</div>
@else
@can('invoice.create')
<form action="{{ route('billing-invoice.generate', $request) }}" method="POST" class="kpi-card mb-3">
    @csrf
    <h6 class="text-primary">Generate Invoice</h6>
    <div class="row g-3">
        <div class="col-md-3"><label class="form-label">Invoice Number</label><input class="form-control" name="invoice_no" placeholder="Auto if blank"></div>
        <div class="col-md-3"><label class="form-label">Invoice Date *</label><input type="date" class="form-control" name="invoice_date" required value="{{ now()->toDateString() }}"></div>
        <div class="col-md-3"><label class="form-label">Billing Month *</label><input class="form-control" name="billing_month" required value="{{ now()->format('M-Y') }}"></div>
        <div class="col-md-3"><label class="form-label">Due Date</label><input type="date" class="form-control" name="due_date"></div>
        <div class="col-md-3"><label class="form-label">Invoice Amount *</label><input type="number" step="0.01" class="form-control" name="invoice_amount" required value="{{ $request->totalSellingPrice() }}"></div>
        <div class="col-md-3"><label class="form-label">VAT</label><input type="number" step="0.01" class="form-control" name="vat_amount" value="0"></div>
        <div class="col-md-3"><label class="form-label">TAX</label><input type="number" step="0.01" class="form-control" name="tax_amount" value="0"></div>
        <div class="col-md-3"><label class="form-label">Other Charges</label><input type="number" step="0.01" class="form-control" name="other_charges" value="0"></div>
        <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks"></textarea></div>
    </div>
    <button class="btn btn-primary mt-3">Mark Invoice Generated</button>
</form>
@endcan
@endif

<div class="kpi-card">
    @can('invoice.mark_sent')
    @if($invoice && !$br?->invoice_sent_at)
    <form action="{{ route('billing-invoice.sent', $request) }}" method="POST" class="d-inline">@csrf<button class="btn btn-outline-primary">Mark Invoice Sent</button></form>
    @endif
    @endcan
    @can('invoice.mark_done')
    @if($br?->invoice_sent_at && !$br?->billing_done_at)
    <form action="{{ route('billing-invoice.done', $request) }}" method="POST" class="d-inline">@csrf<button class="btn btn-success">Mark Billing Done</button></form>
    @endif
    @endcan
</div>
@endsection
