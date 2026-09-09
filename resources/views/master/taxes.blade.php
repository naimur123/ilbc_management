@extends('layouts.app')
@section('title', 'Taxes')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Taxes</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg"></i> Add Tax</button>
</div>
<div class="kpi-card">
    <table class="table table-sm align-middle">
        <thead><tr><th>Name</th><th>Type</th><th>Rate %</th><th>Status</th><th class="text-end">Action</th></tr></thead>
        <tbody>
        @foreach($items as $item)
        <tr>
            <td>{{ $item->name }}</td><td>{{ $item->type }}</td><td>{{ $item->rate_percent }}%</td>
            <td>{!! $item->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}"><i class="bi bi-pencil"></i></button>
                <form action="{{ route('taxes.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    {{ $items->links() }}
</div>

{{-- Edit modals live outside the table — a <div> is not valid inside <tbody> and browsers will otherwise hoist it out, breaking the table layout. --}}
@foreach($items as $item)
<div class="modal fade" id="editModal{{ $item->id }}"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('taxes.update', $item) }}" method="POST">@csrf @method('PUT')
    <div class="modal-header"><h6 class="modal-title">Edit Tax</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-2"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ $item->name }}" required></div>
        <div class="mb-2"><label class="form-label">Type</label>
            <select class="form-select" name="type">
                @foreach(['VAT','TAX','OTHER'] as $t)<option value="{{ $t }}" {{ $item->type === $t ? 'selected' : '' }}>{{ $t }}</option>@endforeach
            </select>
        </div>
        <div class="mb-2"><label class="form-label">Rate %</label><input type="number" step="0.001" class="form-control" name="rate_percent" value="{{ $item->rate_percent }}" required></div>
        <div class="form-check"><input type="checkbox" class="form-check-input" name="is_active" value="1" id="active{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }}><label class="form-check-label" for="active{{ $item->id }}">Active</label></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary btn-sm">Save</button></div>
    </form>
</div></div></div>
@endforeach

<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('taxes.store') }}" method="POST">@csrf
    <div class="modal-header"><h6 class="modal-title">Add Tax</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-2"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
        <div class="mb-2"><label class="form-label">Type</label>
            <select class="form-select" name="type"><option value="VAT">VAT</option><option value="TAX">TAX</option><option value="OTHER">OTHER</option></select>
        </div>
        <div class="mb-2"><label class="form-label">Rate %</label><input type="number" step="0.001" class="form-control" name="rate_percent" required></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary btn-sm">Save</button></div>
    </form>
</div></div></div>
@endsection
