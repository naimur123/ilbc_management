<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#222; }
    h2 { margin-bottom:0; }
    table { width:100%; border-collapse: collapse; margin-top:10px; }
    th, td { border:1px solid #ccc; padding:6px 8px; text-align:left; }
    th { background:#f0f2f5; }
    .text-end { text-align:right; }
    .total-row { font-weight:bold; background:#f9fafb; }
</style>
</head>
<body>
    <h2>{{ config('ilbc.company_name') }}</h2>
    <p>Invoice: <strong>{{ $invoice->invoice_no }}</strong><br>
    Date: {{ $invoice->invoice_date->format('d-m-Y') }} | Billing Month: {{ $invoice->billing_month }}<br>
    Request: {{ $invoice->request->request_no }} | Customer: {{ $invoice->request->customer->name ?? '-' }}</p>

    <table>
        <thead><tr><th>Description</th><th>Qty</th><th>Unit Price</th><th>Line Total</th></tr></thead>
        <tbody>
        @foreach($invoice->items as $item)
            <tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td class="text-end">{{ number_format($item->unit_price,2) }}</td><td class="text-end">{{ number_format($item->line_total,2) }}</td></tr>
        @endforeach
            <tr><td colspan="3" class="text-end">Subtotal</td><td class="text-end">{{ number_format($invoice->invoice_amount,2) }}</td></tr>
            <tr><td colspan="3" class="text-end">VAT</td><td class="text-end">{{ number_format($invoice->vat_amount,2) }}</td></tr>
            <tr><td colspan="3" class="text-end">TAX</td><td class="text-end">{{ number_format($invoice->tax_amount,2) }}</td></tr>
            <tr><td colspan="3" class="text-end">Other Charges</td><td class="text-end">{{ number_format($invoice->other_charges,2) }}</td></tr>
            <tr class="total-row"><td colspan="3" class="text-end">Total ({{ config('ilbc.currency_code') }})</td><td class="text-end">{{ number_format($invoice->total_amount,2) }}</td></tr>
        </tbody>
    </table>

    <p style="margin-top:20px;">Due Date: {{ optional($invoice->due_date)->format('d-m-Y') ?? '-' }}</p>
    @if($invoice->remarks)<p>Remarks: {{ $invoice->remarks }}</p>@endif
</body>
</html>
