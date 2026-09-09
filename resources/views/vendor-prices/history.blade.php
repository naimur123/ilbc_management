@extends('layouts.app')
@section('title', 'Vendor Price History')
@section('content')
<h4 class="mb-3">Vendor Price History</h4>
<div class="kpi-card">
    <div class="table-responsive">
    <table class="table table-sm align-middle">
        <thead><tr><th>Vendor</th><th>SKU</th><th>Old Price</th><th>New Price</th><th>Effective From</th><th>Effective To</th><th>Changed At</th></tr></thead>
        <tbody>
        @forelse($history as $h)
        <tr>
            <td>{{ $h->vendor->name }}</td><td>{{ $h->sku->sku_code }}</td>
            <td>{{ $h->old_unit_purchase_price !== null ? number_format($h->old_unit_purchase_price, 2) : '-' }}</td>
            <td>{{ number_format($h->new_unit_purchase_price, 2) }}</td>
            <td>{{ $h->effective_from->format('d-m-Y') }}</td>
            <td>{{ $h->effective_to?->format('d-m-Y') ?? '-' }}</td>
            <td>{{ $h->changed_at->format('d-m-Y H:i') }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center text-muted py-3">No price changes recorded yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    {{ $history->links() }}
</div>
@endsection
