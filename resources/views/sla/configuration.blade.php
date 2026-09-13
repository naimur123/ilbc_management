@extends('layouts.app')
@section('title', 'SLA Configuration')
@section('content')
<h4 class="mb-3">SLA Configuration</h4>
<p class="text-muted small">Admin-defined SLA rules by Product / Category / Customer / Department / Priority (Section "SLA Configuration"). When more than one rule matches a request, the most specific one wins — a customer + product specific rule beats a generic one.</p>

<div class="kpi-card mb-3">
    <h6 class="text-primary">SLA Completion Required Before Audit</h6>
    <form action="{{ route('sla.configuration.settings') }}" method="POST" class="d-flex align-items-center gap-3">
        @csrf
        <div class="form-check form-switch">
            <input type="hidden" name="completion_required_before_audit" value="0">
            <input class="form-check-input" type="checkbox" role="switch" name="completion_required_before_audit" value="1" id="requireBeforeAudit" {{ $requireBeforeAudit ? 'checked' : '' }} onchange="this.form.submit()">
            <label class="form-check-label" for="requireBeforeAudit">{{ $requireBeforeAudit ? 'Yes' : 'No' }} — {{ $requireBeforeAudit ? 'Auditors are blocked from approving a request while its SLA is still open.' : 'SLA is tracked as a performance measurement only; Audit can proceed regardless of SLA status.' }}</label>
        </div>
    </form>
</div>

<div class="kpi-card p-0 mb-3">
    <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead class="table-light"><tr><th>Name</th><th>Type</th><th>Product</th><th>Category</th><th>Customer</th><th>Department</th><th>Priority</th><th>Duration</th><th>Reminder</th><th>Escalate</th><th>Team</th><th>Active</th><th></th></tr></thead>
        <tbody>
        @forelse($configurations as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td>{{ ucfirst(strtolower($c->sla_type)) }}</td>
                <td>{{ $c->product->name ?? 'Any' }}</td>
                <td>{{ $c->productCategory->name ?? 'Any' }}</td>
                <td>{{ $c->customer->name ?? 'Any' }}</td>
                <td>{{ $c->department->name ?? 'Any' }}</td>
                <td>{{ $c->priority ? ucfirst(strtolower($c->priority)) : 'Any' }}</td>
                <td>{{ intdiv($c->duration_minutes,60) }}h {{ $c->duration_minutes % 60 }}m</td>
                <td>{{ $c->reminder_before_minutes }}m before</td>
                <td>{{ $c->escalate_after_minutes }}m after</td>
                <td>{{ $c->responsible_team ?? '-' }}</td>
                <td>{!! $c->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
                <td class="text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCfg{{ $c->id }}"><i class="bi bi-pencil"></i></button>
                    <form action="{{ route('sla.configuration.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this SLA configuration?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="13" class="text-center text-muted py-4">No SLA configurations yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>

<div class="kpi-card">
    <h6 class="text-primary">Add SLA Configuration</h6>
    <form action="{{ route('sla.configuration.store') }}" method="POST" class="row g-3">
        @csrf
        <div class="col-md-3"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
        <div class="col-md-2"><label class="form-label">SLA Type</label>
            <select class="form-select" name="sla_type" required>
                <option value="ACTIVATION">Activation</option>
                <option value="DELIVERY">Delivery</option>
                <option value="RESPONSE">Response</option>
                <option value="RESOLUTION">Resolution</option>
                <option value="CUSTOM">Custom</option>
            </select>
        </div>
        <div class="col-md-2"><label class="form-label">Product (optional)</label>
            <select class="form-select" name="product_id"><option value="">Any</option>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select>
        </div>
        <div class="col-md-2"><label class="form-label">Category (optional)</label>
            <select class="form-select" name="product_category_id"><option value="">Any</option>@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select>
        </div>
        <div class="col-md-3"><label class="form-label">Customer (optional)</label>
            <select class="form-select" name="customer_id"><option value="">Any</option>@foreach($customers as $cu)<option value="{{ $cu->id }}">{{ $cu->name }}</option>@endforeach</select>
        </div>
        <div class="col-md-3"><label class="form-label">Department (optional)</label>
            <select class="form-select" name="department_id"><option value="">Any</option>@foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select>
        </div>
        <div class="col-md-2"><label class="form-label">Priority (optional)</label>
            <select class="form-select" name="priority">
                <option value="">Any</option>
                <option value="LOW">Low</option><option value="NORMAL">Normal</option><option value="HIGH">High</option><option value="CRITICAL">Critical</option>
            </select>
        </div>
        <div class="col-md-2"><label class="form-label">Duration (minutes)</label><input type="number" class="form-control" name="duration_minutes" min="1" required></div>
        <div class="col-md-2"><label class="form-label">Reminder Before (min)</label><input type="number" class="form-control" name="reminder_before_minutes" value="60" min="0"></div>
        <div class="col-md-2"><label class="form-label">Escalate After (min)</label><input type="number" class="form-control" name="escalate_after_minutes" value="120" min="0"></div>
        <div class="col-md-3"><label class="form-label">Responsible Team</label><input class="form-control" name="responsible_team" placeholder="NOC / Support / Billing / Operations"></div>
        <div class="col-md-12 form-check">
            <input type="checkbox" class="form-check-input" name="is_active" value="1" id="isActiveNew" checked>
            <label class="form-check-label" for="isActiveNew">Active</label>
        </div>
        <div class="col-12"><button class="btn btn-primary btn-sm">Add Configuration</button></div>
    </form>
</div>

@foreach($configurations as $c)
<div class="modal fade" id="editCfg{{ $c->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('sla.configuration.update', $c) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-header"><h6 class="modal-title">Edit — {{ $c->name }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-md-4"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ $c->name }}" required></div>
                    <div class="col-md-4"><label class="form-label">SLA Type</label>
                        <select class="form-select" name="sla_type" required>
                            @foreach(['ACTIVATION','DELIVERY','RESPONSE','RESOLUTION','CUSTOM'] as $t)
                            <option value="{{ $t }}" {{ $c->sla_type===$t?'selected':'' }}>{{ ucfirst(strtolower($t)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4"><label class="form-label">Priority</label>
                        <select class="form-select" name="priority">
                            <option value="">Any</option>
                            @foreach(['LOW','NORMAL','HIGH','CRITICAL'] as $p)
                            <option value="{{ $p }}" {{ $c->priority===$p?'selected':'' }}>{{ ucfirst(strtolower($p)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Product</label>
                        <select class="form-select" name="product_id"><option value="">Any</option>@foreach($products as $p)<option value="{{ $p->id }}" {{ $c->product_id==$p->id?'selected':'' }}>{{ $p->name }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Category</label>
                        <select class="form-select" name="product_category_id"><option value="">Any</option>@foreach($categories as $cat)<option value="{{ $cat->id }}" {{ $c->product_category_id==$cat->id?'selected':'' }}>{{ $cat->name }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Customer</label>
                        <select class="form-select" name="customer_id"><option value="">Any</option>@foreach($customers as $cu)<option value="{{ $cu->id }}" {{ $c->customer_id==$cu->id?'selected':'' }}>{{ $cu->name }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Department</label>
                        <select class="form-select" name="department_id"><option value="">Any</option>@foreach($departments as $d)<option value="{{ $d->id }}" {{ $c->department_id==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-4"><label class="form-label">Duration (minutes)</label><input type="number" class="form-control" name="duration_minutes" value="{{ $c->duration_minutes }}" min="1" required></div>
                    <div class="col-md-4"><label class="form-label">Reminder Before (min)</label><input type="number" class="form-control" name="reminder_before_minutes" value="{{ $c->reminder_before_minutes }}" min="0"></div>
                    <div class="col-md-4"><label class="form-label">Escalate After (min)</label><input type="number" class="form-control" name="escalate_after_minutes" value="{{ $c->escalate_after_minutes }}" min="0"></div>
                    <div class="col-md-8"><label class="form-label">Responsible Team</label><input class="form-control" name="responsible_team" value="{{ $c->responsible_team }}"></div>
                    <div class="col-md-4 form-check pt-4">
                        <input type="checkbox" class="form-check-input" name="is_active" value="1" id="isActive{{ $c->id }}" {{ $c->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive{{ $c->id }}">Active</label>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary btn-sm">Save Changes</button></div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
