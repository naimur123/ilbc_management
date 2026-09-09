@extends('layouts.app')
@section('title', $request->request_no)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">{{ $request->request_no }} <span class="badge bg-primary-subtle text-primary-emphasis">{{ str_replace('_',' ',$request->status) }}</span></h4>
        <small class="text-muted">Created {{ $request->created_at->format('d-m-Y H:i') }} by {{ $request->creator->name ?? '-' }}</small>
    </div>
    <div>
        @can('request.edit')
        @if($request->status === 'DRAFT')
            <a href="{{ route('requests.edit', $request) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
            <form action="{{ route('requests.submit', $request) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-primary">Submit</button></form>
        @endif
        @endcan
        @can('request.cancel')
        @if(!in_array($request->status, ['CLOSED','CANCELLED']))
        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">Cancel</button>
        @endif
        @endcan
        @can('request.reopen')
        @if($request->status === 'CLOSED')
        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#reopenModal">Request Reopen</button>
        @endif
        @endcan
    </div>
</div>

{{-- Workflow Timeline (Section 29) --}}
<div class="kpi-card mb-3">
    <div class="d-flex">
        @foreach($timeline as $t)
            <div class="timeline-step timeline-{{ strtolower($t['state']) }}">
                <div class="timeline-dot mx-auto"><i class="bi {{ $t['state'] === 'DONE' ? 'bi-check-lg' : ($t['state'] === 'CURRENT' ? 'bi-arrow-right' : 'bi-hourglass') }}"></i></div>
                <div class="small mt-1 fw-semibold">{{ $t['step']->name }}</div>
                <div class="small text-muted">{{ $t['state'] }}</div>
            </div>
        @endforeach
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="kpi-card h-100">
            <h6 class="text-primary">Customer & Order</h6>
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Customer</th><td>{{ $request->customer->name ?? '-' }}</td></tr>
                <tr><th class="text-muted">Salesperson</th><td>{{ $request->salesperson->name ?? '-' }}</td></tr>
                <tr><th class="text-muted">Work Order</th><td>{{ $request->work_order_no ?: '-' }}</td></tr>
                <tr><th class="text-muted">PO Number</th><td>{{ $request->po_number ?: '-' }}</td></tr>
                <tr><th class="text-muted">Contact</th><td>{{ $request->salesEntry->contact_person ?? '-' }} / {{ $request->salesEntry->mobile ?? '-' }}</td></tr>
                <tr><th class="text-muted">Payment Terms</th><td>{{ $request->salesEntry->paymentTerm->name ?? '-' }} ({{ $request->salesEntry->advance_percent ?? 0 }}% advance, {{ $request->salesEntry->credit_days ?? 0 }} days credit)</td></tr>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="kpi-card h-100">
            <h6 class="text-primary">Financial Overview</h6>
            <table class="table table-sm mb-0">
                <tr><th class="text-muted">Total Selling Price</th><td>{{ config('ilbc.currency_symbol') }} {{ number_format($request->totalSellingPrice(), 2) }}</td></tr>
                @can('sales.view_cost')
                <tr><th class="text-muted">Final Landed Cost</th><td>{{ config('ilbc.currency_symbol') }} {{ number_format($request->items->sum(fn($i) => $i->vendorSelection->final_landed_cost ?? 0), 2) }}</td></tr>
                @endcan
                @can('sales.view_margin')
                @php($profit = $request->totalSellingPrice() - $request->items->sum(fn($i) => $i->vendorSelection->final_landed_cost ?? 0))
                <tr><th class="text-muted">Expected Profit</th><td class="{{ $profit >= 0 ? 'text-success' : 'text-danger' }}">{{ config('ilbc.currency_symbol') }} {{ number_format($profit, 2) }}</td></tr>
                <tr><th class="text-muted">Gross Margin %</th><td>{{ $request->totalSellingPrice() > 0 ? number_format($profit / $request->totalSellingPrice() * 100, 2) : '0.00' }}%</td></tr>
                @endcan
                <tr><th class="text-muted">Current Stage</th><td><span class="badge bg-light text-dark border">{{ str_replace('_',' ',$request->current_stage) }}</span></td></tr>
            </table>
        </div>
    </div>
</div>

{{-- Product / Vendor / Loading / Audit per item --}}
<div class="kpi-card mb-3 p-0">
    <div class="p-3 pb-0"><h6 class="text-primary">Product Items</h6></div>
    <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Product / SKU</th><th>Qty</th><th>Unit Price</th><th>Total Selling</th>
                <th>Vendor</th>
                @can('sales.view_cost')<th>Final Cost</th>@endcan
                @can('sales.view_margin')<th>Margin %</th>@endcan
                <th>Loading</th><th>Audit</th><th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($request->items as $item)
            <tr>
                <td>{{ $item->product->name ?? '-' }}<br><small class="text-muted">{{ $item->sku->sku_code ?? '' }}</small></td>
                <td>{{ rtrim(rtrim(number_format($item->quantity,2),'0'),'.') }}</td>
                <td>{{ number_format($item->unit_selling_price,2) }}</td>
                <td>{{ number_format($item->total_selling_price,2) }}</td>
                <td>{{ $item->vendorSelection->vendor->name ?? '—' }}</td>
                @can('sales.view_cost')<td>{{ $item->vendorSelection ? number_format($item->vendorSelection->final_landed_cost,2) : '—' }}</td>@endcan
                @can('sales.view_margin')<td>{{ $item->vendorSelection ? number_format($item->vendorSelection->gross_margin_percent,2).'%' : '—' }}</td>@endcan
                <td>
                    @if($item->loadingRecord?->status === 'COMPLETED')<span class="badge bg-success">Completed</span>
                    @elseif($item->loadingRecord)<span class="badge bg-warning text-dark">In Progress</span>
                    @else<span class="badge bg-secondary">Pending</span>@endif
                </td>
                <td>
                    @if($item->auditRecord?->decision === 'APPROVE')<span class="badge bg-teal text-white" style="background:#0d9488;">Approved</span>
                    @elseif($item->auditRecord)<span class="badge bg-danger">{{ $item->auditRecord->decision }}</span>
                    @else<span class="badge bg-secondary">Pending</span>@endif
                </td>
                <td>
                    @can('vendor.select')
                    @if(in_array($request->status, ['REVIEWER_PENDING','BILLING_CLEARED']))
                    <a href="{{ route('requests.items.vendor-comparison', [$request, $item]) }}" class="btn btn-sm btn-outline-primary">Compare Vendors</a>
                    @endif
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="kpi-card">
            <h6 class="text-primary">Attachments</h6>
            @forelse($request->attachments as $a)
                <div class="small mb-1"><i class="bi bi-paperclip"></i> <a href="{{ \Illuminate\Support\Facades\Storage::url($a->path) }}" target="_blank">{{ $a->original_name }}</a> <span class="text-muted">({{ $a->type }})</span></div>
            @empty
                <div class="text-muted small">No attachments.</div>
            @endforelse
        </div>
    </div>
    <div class="col-md-5">
        <div class="kpi-card">
            <h6 class="text-primary">Comments</h6>
            <div style="max-height:180px;overflow-y:auto;">
            @forelse($request->comments as $c)
                <div class="small border-bottom py-1"><strong>{{ $c->user->name ?? '-' }}</strong>: {{ $c->body }} <span class="text-muted">({{ $c->created_at->diffForHumans() }})</span></div>
            @empty
                <div class="text-muted small">No comments yet.</div>
            @endforelse
            </div>
            <form action="{{ route('requests.comments.store', $request) }}" method="POST" class="d-flex gap-2 mt-2">
                @csrf
                <input class="form-control form-control-sm" name="body" placeholder="Add a comment..." required>
                <button class="btn btn-sm btn-outline-primary">Post</button>
            </form>
        </div>
    </div>
</div>

@can('request.cancel')
<div class="modal fade" id="cancelModal"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('requests.cancel', $request) }}" method="POST">
    @csrf
    <div class="modal-header"><h6 class="modal-title">Cancel Request</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><label class="form-label">Reason *</label><textarea class="form-control" name="reason" required></textarea></div>
    <div class="modal-footer"><button class="btn btn-danger">Confirm Cancel</button></div>
    </form>
</div></div></div>
@endcan

@can('request.reopen')
<div class="modal fade" id="reopenModal"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('requests.reopen', $request) }}" method="POST">
    @csrf
    <div class="modal-header"><h6 class="modal-title">Request Reopen</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <label class="form-label">Reopen to Stage *</label>
        <select class="form-select mb-2" name="target_stage" required>
            @foreach(\App\Models\Request::STAGES as $stage)<option value="{{ $stage }}">{{ str_replace('_',' ',$stage) }}</option>@endforeach
        </select>
        <label class="form-label">Reason *</label><textarea class="form-control" name="reason" required></textarea>
    </div>
    <div class="modal-footer"><button class="btn btn-warning">Submit for Approval</button></div>
    </form>
</div></div></div>
@endcan
@endsection
