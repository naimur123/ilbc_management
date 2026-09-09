@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Dashboard</h4>
        <small class="text-muted">Welcome back, {{ auth()->user()->name }}</small>
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach([
        ['Total Requests', $kpi['total'], 'bi-file-earmark-text', 'primary'],
        ['This Month', $kpi['this_month'], 'bi-calendar3', 'info'],
        ['Pending', $kpi['pending'], 'bi-hourglass-split', 'warning'],
        ['Billing Clearance', $kpi['billing_clearance_pending'], 'bi-shield-check', 'primary'],
        ['Reviewer Pending', $kpi['reviewer_pending'], 'bi-person-check', 'purple'],
        ['Loading Pending', $kpi['loading_pending'], 'bi-box-seam', 'warning'],
        ['Audit Pending', $kpi['audit_pending'], 'bi-clipboard-data', 'teal'],
        ['Billing Pending', $kpi['billing_pending'], 'bi-receipt', 'info'],
        ['Completed', $kpi['completed'], 'bi-check-circle', 'success'],
        ['Closed', $kpi['closed'], 'bi-lock', 'success'],
        ['Rejected / Hold', $kpi['rejected'] + $kpi['on_hold'], 'bi-x-circle', 'danger'],
        ['SLA Overdue', $kpi['sla_overdue'], 'bi-exclamation-triangle', 'danger'],
    ] as [$label, $value, $icon, $color])
    <div class="col-6 col-md-4 col-xl-2">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="kpi-value">{{ number_format($value) }}</div>
                </div>
                <i class="bi {{ $icon }} fs-4 text-{{ $color === 'purple' ? 'dark' : ($color === 'teal' ? 'dark' : $color) }}"></i>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-4">
        <div class="kpi-card">
            <h6>Requests by Status</h6>
            <canvas id="statusDonut" height="220"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="kpi-card">
            <h6>SLA Overview (Pending Aging)</h6>
            <canvas id="slaDonut" height="220"></canvas>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="kpi-card">
            <h6>Monthly Request Flow</h6>
            <canvas id="flowBar" height="220"></canvas>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="kpi-card">
            <div class="d-flex justify-content-between"><h6>My Pending Tasks</h6></div>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr><th>Request ID</th><th>Customer</th><th>Stage</th><th>Pending Since</th><th>SLA</th><th></th></tr></thead>
                    <tbody>
                    @forelse($pendingTasks as $r)
                        <tr>
                            <td><a href="{{ route('requests.show', $r) }}">{{ $r->request_no }}</a></td>
                            <td>{{ $r->customer->name }}</td>
                            <td><span class="badge bg-light text-dark border">{{ ucfirst(strtolower(str_replace('_',' ',$r->current_stage))) }}</span></td>
                            <td>{{ $sla->hoursPending($r) }}h</td>
                            <td class="status-{{ strtolower(str_replace('_','-',$sla->statusFor($r))) }}">{{ str_replace('_',' ',$sla->statusFor($r)) }}</td>
                            <td><a href="{{ route('requests.show', $r) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">No pending tasks</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="kpi-card">
            <h6>Financial Summary (This Month)</h6>
            <table class="table table-sm">
                <tr><td>Total Sales</td><td class="text-end">{{ config('ilbc.currency_symbol') }} {{ number_format($financial['total_sales'], 2) }}</td></tr>
                <tr><td>Vendor Purchase Value</td><td class="text-end">{{ config('ilbc.currency_symbol') }} {{ number_format($financial['vendor_purchase_value'], 2) }}</td></tr>
                <tr><td>Invoice Value</td><td class="text-end">{{ config('ilbc.currency_symbol') }} {{ number_format($financial['invoice_value'], 2) }}</td></tr>
                <tr><td>Gross Profit</td><td class="text-end text-success">{{ config('ilbc.currency_symbol') }} {{ number_format($financial['gross_profit'], 2) }}</td></tr>
                <tr><td>Gross Margin %</td><td class="text-end text-success">{{ $financial['gross_margin_percent'] }}%</td></tr>
            </table>
        </div>
    </div>
</div>

<div class="kpi-card">
    <h6>Recent Activity</h6>
    <ul class="list-unstyled small mb-0">
        @foreach($recentActivity as $log)
        <li class="border-bottom py-2">
            <strong>{{ $log->action }}</strong> @if($log->request) on <a href="{{ route('requests.show', $log->request) }}">{{ $log->request->request_no }}</a>@endif
            <span class="text-muted"> — {{ $log->user->name ?? 'System' }}, {{ $log->created_at->diffForHumans() }}</span>
        </li>
        @endforeach
    </ul>
</div>

@push('scripts')
<script>
new Chart(document.getElementById('statusDonut'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($statusByStage->keys()) !!},
        datasets: [{ data: {!! json_encode($statusByStage->values()) !!}, backgroundColor: ['#2563eb','#7c3aed','#f59e0b','#0d9488','#16a34a','#dc2626','#64748b'] }]
    },
    options: { plugins: { legend: { position: 'right', labels: { boxWidth: 10 } } } }
});
new Chart(document.getElementById('slaDonut'), {
    type: 'doughnut',
    data: {
        labels: ['On Time', 'Due Soon', 'Overdue'],
        datasets: [{ data: [{{ $slaBuckets['ON_TIME'] }}, {{ $slaBuckets['DUE_SOON'] }}, {{ $slaBuckets['OVERDUE'] }}], backgroundColor: ['#16a34a','#f59e0b','#dc2626'] }]
    },
    options: { plugins: { legend: { position: 'right' } } }
});
new Chart(document.getElementById('flowBar'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($monthlyFlow)) !!},
        datasets: [{ label: 'Requests', data: {!! json_encode(array_values($monthlyFlow)) !!}, backgroundColor: '#2563eb' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { x: { ticks: { autoSkip: false, font: { size: 9 } } } } }
});
</script>
@endpush
@endsection
