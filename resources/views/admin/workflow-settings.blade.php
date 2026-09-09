@extends('layouts.app')
@section('title', 'Workflow & Settings')
@section('content')
<h4 class="mb-3">Workflow & Settings</h4>

<form action="{{ route('workflow-settings.update') }}" method="POST">
@csrf @method('PUT')

<div class="kpi-card mb-3">
    <h6 class="text-primary">Workflow Steps ({{ $workflow->name ?? 'Default' }})</h6>
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>#</th><th>Step</th><th>Key</th><th>Required Permission</th></tr></thead>
        <tbody>
        @foreach($workflow->steps ?? [] as $step)
            <tr><td>{{ $step->step_order }}</td><td>{{ $step->name }}</td><td><code>{{ $step->step_key }}</code></td><td>{{ $step->required_permission ?? '-' }}</td></tr>
        @endforeach
        </tbody>
    </table>
    <p class="text-muted small mt-2 mb-0">Step order/labels are data-driven (Section 3) — re-ordering here does not require code changes. Contact an administrator to add or remove entire stages.</p>
</div>

<div class="kpi-card mb-3">
    <h6 class="text-primary">SLA Rules (Section 34)</h6>
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Stage</th><th>SLA (hours)</th><th>Due Soon Threshold (hours)</th></tr></thead>
        <tbody>
        @foreach($slaRules as $rule)
            <tr>
                <td>{{ $rule->label }}</td>
                <td><input type="number" class="form-control form-control-sm" name="sla[{{ $rule->id }}][sla_hours]" value="{{ $rule->sla_hours }}"></td>
                <td><input type="number" class="form-control form-control-sm" name="sla[{{ $rule->id }}][due_soon_threshold_hours]" value="{{ $rule->due_soon_threshold_hours }}"></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="kpi-card mb-3">
    <h6 class="text-primary">Conditional Approval Rules (Section 17)</h6>
    <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Rule</th><th>Condition</th><th>Threshold</th><th>Active</th></tr></thead>
        <tbody>
        @foreach($approvalRules as $rule)
            <tr>
                <td>{{ $rule->name }}</td>
                <td><code>{{ $rule->condition_field }} {{ $rule->operator }}</code></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm" name="approval[{{ $rule->id }}][threshold_value]" value="{{ $rule->threshold_value }}"></td>
                <td><input type="checkbox" class="form-check-input" name="approval[{{ $rule->id }}][is_active]" value="1" {{ $rule->is_active ? 'checked' : '' }}></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<button class="btn btn-primary">Save Settings</button>
</form>
@endsection
