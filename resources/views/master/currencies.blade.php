@extends('layouts.app')
@section('title', 'Currencies')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Currencies</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg"></i> Add Currency</button>
</div>
<div class="kpi-card">
    <table class="table table-sm align-middle">
        <thead><tr><th>Code</th><th>Name</th><th>Symbol</th><th>Rate to Base</th><th>Status</th><th class="text-end">Action</th></tr></thead>
        <tbody>
        @foreach($items as $item)
        <tr>
            <td>{{ $item->code }}</td><td>{{ $item->name }}</td><td>{{ $item->symbol }}</td><td>{{ $item->exchange_rate_to_base }}</td>
            <td>{!! $item->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}"><i class="bi bi-pencil"></i></button>
                <form action="{{ route('currencies.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
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
    <form action="{{ route('currencies.update', $item) }}" method="POST">@csrf @method('PUT')
    <div class="modal-header"><h6 class="modal-title">Edit Currency</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-2"><label class="form-label">Code</label><input class="form-control" name="code" value="{{ $item->code }}" required></div>
        <div class="mb-2"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ $item->name }}" required></div>
        <div class="mb-2"><label class="form-label">Symbol</label><input class="form-control" name="symbol" value="{{ $item->symbol }}" required></div>
        <div class="mb-2"><label class="form-label">Exchange Rate to Base</label><input type="number" step="0.0001" class="form-control" name="exchange_rate_to_base" value="{{ $item->exchange_rate_to_base }}" required></div>
        <div class="form-check"><input type="checkbox" class="form-check-input" name="is_active" value="1" id="active{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }}><label class="form-check-label" for="active{{ $item->id }}">Active</label></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary btn-sm">Save</button></div>
    </form>
</div></div></div>
@endforeach

<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('currencies.store') }}" method="POST">@csrf
    <div class="modal-header"><h6 class="modal-title">Add Currency</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-2"><label class="form-label">Code</label><input class="form-control" name="code" required></div>
        <div class="mb-2"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
        <div class="mb-2"><label class="form-label">Symbol</label><input class="form-control" name="symbol" required></div>
        <div class="mb-2"><label class="form-label">Exchange Rate to Base</label><input type="number" step="0.0001" class="form-control" name="exchange_rate_to_base" value="1" required></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary btn-sm">Save</button></div>
    </form>
</div></div></div>
@endsection
