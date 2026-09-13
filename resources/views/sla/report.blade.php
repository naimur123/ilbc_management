@extends('layouts.app')
@section('title', 'SLA Performance Report')
@section('content')
<h4 class="mb-3">SLA Performance Report</h4>

<div class="row g-3 mb-3">
    @foreach([
        ['SLA Compliance %', $compliancePercent.'%', 'bi-graph-up-arrow', $compliancePercent >= 90 ? 'success' : ($compliancePercent >= 75 ? 'warning' : 'danger')],
        ['Total Completed', $totalCompleted, 'bi-check2-square', 'primary'],
        ['Completed Within SLA', $withinSla, 'bi-check-circle', 'success'],
        ['Completed Late', $totalCompleted - $withinSla, 'bi-x-circle', 'danger'],
        ['Avg. Completion Time', intdiv($avgCompletionMinutes,60).'h '.($avgCompletionMinutes%60).'m', 'bi-stopwatch', 'info'],
        ['Due Soon (Open)', $openCounts['DUE_SOON'] ?? 0, 'bi-hourglass-split', 'warning'],
        ['Overdue (Open)', $openCounts['OVERDUE'] ?? 0, 'bi-exclamation-triangle', 'danger'],
    ] as [$label, $value, $icon, $color])
    <div class="col-6 col-md-4 col-xl-3">
        <div class="kpi-card">
            <div class="d-flex justify-content-between align-items-start">
                <div><div class="text-muted small">{{ $label }}</div><div class="kpi-value">{{ $value }}</div></div>
                <i class="bi {{ $icon }} fs-4 text-{{ $color }}"></i>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-4">
        <div class="kpi-card">
            <h6 class="text-primary">Customer-wise SLA</h6>
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Customer</th><th>Total</th><th>Within SLA</th><th>%</th></tr></thead>
                <tbody>
                @forelse($byCustomer as $name => $row)
                    <tr><td>{{ $name }}</td><td>{{ $row['total'] }}</td><td>{{ $row['within'] }}</td><td>{{ $row['total']>0 ? round($row['within']/$row['total']*100,1) : 0 }}%</td></tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No data yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="kpi-card">
            <h6 class="text-primary">Product-wise SLA</h6>
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Product</th><th>Total</th><th>Within SLA</th><th>%</th></tr></thead>
                <tbody>
                @forelse($byProduct as $name => $row)
                    <tr><td>{{ $name }}</td><td>{{ $row['total'] }}</td><td>{{ $row['within'] }}</td><td>{{ $row['total']>0 ? round($row['within']/$row['total']*100,1) : 0 }}%</td></tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No data yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="kpi-card">
            <h6 class="text-primary">Employee-wise SLA</h6>
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Responsible</th><th>Total</th><th>Within SLA</th><th>%</th></tr></thead>
                <tbody>
                @forelse($byResponsible as $name => $row)
                    <tr><td>{{ $name }}</td><td>{{ $row['total'] }}</td><td>{{ $row['within'] }}</td><td>{{ $row['total']>0 ? round($row['within']/$row['total']*100,1) : 0 }}%</td></tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">No data yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="kpi-card">
    <h6 class="text-primary">Monthly SLA Trend</h6>
    <canvas id="slaTrend" height="90"></canvas>
</div>

@push('scripts')
<script>
new Chart(document.getElementById('slaTrend'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlyTrend->keys()) !!},
        datasets: [
            { label: 'Within SLA', data: {!! json_encode($monthlyTrend->pluck('within')->values()) !!}, backgroundColor: '#16a34a' },
            { label: 'Late', data: {!! json_encode($monthlyTrend->map(fn($r) => $r['total'] - $r['within'])->values()) !!}, backgroundColor: '#dc2626' },
        ]
    },
    options: { responsive: true, scales: { x: { stacked: true }, y: { stacked: true } } }
});
</script>
@endpush
@endsection
