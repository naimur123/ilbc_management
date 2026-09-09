@extends('layouts.app')
@section('title', 'Product Categories')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Product Categories</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg"></i> Add Category</button>
</div>
<div class="kpi-card">
    <table class="table table-sm align-middle">
        <thead><tr><th>Name</th><th>Code</th><th>Publisher</th><th>Products</th><th>Status</th><th class="text-end">Action</th></tr></thead>
        <tbody>
        @foreach($items as $item)
        <tr>
            <td>{{ $item->name }}</td><td>{{ $item->code }}</td><td>{{ $item->publisher }}</td><td>{{ $item->products()->count() }}</td>
            <td>{!! $item->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}"><i class="bi bi-pencil"></i></button>
                <form action="{{ route('product-categories.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
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
    <form action="{{ route('product-categories.update', $item) }}" method="POST">@csrf @method('PUT')
    <div class="modal-header"><h6 class="modal-title">Edit Category</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-2"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ $item->name }}" required></div>
        <div class="mb-2"><label class="form-label">Code</label><input class="form-control" name="code" value="{{ $item->code }}" required></div>
        <div class="mb-2"><label class="form-label">Publisher</label><input class="form-control" name="publisher" value="{{ $item->publisher }}"></div>
        <div class="form-check"><input type="checkbox" class="form-check-input" name="is_active" value="1" id="active{{ $item->id }}" {{ $item->is_active ? 'checked' : '' }}><label class="form-check-label" for="active{{ $item->id }}">Active</label></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary btn-sm">Save</button></div>
    </form>
</div></div></div>
@endforeach

<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('product-categories.store') }}" method="POST">@csrf
    <div class="modal-header"><h6 class="modal-title">Add Category</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-2"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
        <div class="mb-2"><label class="form-label">Code</label><input class="form-control" name="code" required placeholder="e.g. M365, AZURE"></div>
        <div class="mb-2"><label class="form-label">Publisher</label><input class="form-control" name="publisher" value="Microsoft"></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary btn-sm">Save</button></div>
    </form>
</div></div></div>
@endsection
