@extends('layouts.app')
@section('title', 'Products')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Products</h4>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg"></i> Add Product</button>
</div>
<div class="kpi-card">
    <form class="mb-3 row g-2" method="GET">
        <div class="col-auto"><select class="form-select form-select-sm" name="category_id" onchange="this.form.submit()">
            <option value="">All Categories</option>
            @foreach($categories as $c)<option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
        </select></div>
    </form>
    <div class="table-responsive">
    <table class="table table-sm align-middle">
        <thead><tr><th>Name</th><th>Category</th><th>Line</th><th>Segment</th><th>Licensing</th><th>SKUs</th><th>Status</th><th class="text-end">Action</th></tr></thead>
        <tbody>
        @foreach($products as $p)
        <tr>
            <td>{{ $p->name }}</td><td>{{ $p->category->name }}</td><td>{{ $p->product_line }}</td><td>{{ $p->segment }}</td><td>{{ $p->licensing_program }}</td>
            <td><a href="{{ route('product-skus.index', ['product_id' => $p->id]) }}">{{ $p->skus()->count() }}</a></td>
            <td>{!! $p->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editModal{{ $p->id }}"><i class="bi bi-pencil"></i></button>
                <form action="{{ route('products.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    {{ $products->links() }}
</div>

{{-- Edit modals live outside the table — a <div> is not valid inside <tbody> and browsers will otherwise hoist it out, breaking the table layout. --}}
@foreach($products as $p)
<div class="modal fade" id="editModal{{ $p->id }}"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('products.update', $p) }}" method="POST">@csrf @method('PUT')
    <div class="modal-header"><h6 class="modal-title">Edit Product</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-2"><label class="form-label">Category</label><select class="form-select" name="product_category_id">@foreach($categories as $c)<option value="{{ $c->id }}" {{ $p->product_category_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
        <div class="mb-2"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ $p->name }}" required></div>
        <div class="mb-2"><label class="form-label">Product Line</label><input class="form-control" name="product_line" value="{{ $p->product_line }}"></div>
        <div class="mb-2"><label class="form-label">Segment</label><input class="form-control" name="segment" value="{{ $p->segment }}"></div>
        <div class="mb-2"><label class="form-label">Licensing Program</label><input class="form-control" name="licensing_program" value="{{ $p->licensing_program }}"></div>
        <div class="form-check"><input type="checkbox" class="form-check-input" name="is_active" value="1" id="active{{ $p->id }}" {{ $p->is_active ? 'checked' : '' }}><label class="form-check-label" for="active{{ $p->id }}">Active</label></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary btn-sm">Save</button></div>
    </form>
</div></div></div>
@endforeach

<div class="modal fade" id="addModal"><div class="modal-dialog"><div class="modal-content">
    <form action="{{ route('products.store') }}" method="POST">@csrf
    <div class="modal-header"><h6 class="modal-title">Add Product</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-2"><label class="form-label">Category</label><select class="form-select" name="product_category_id" required>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
        <div class="mb-2"><label class="form-label">Name</label><input class="form-control" name="name" required></div>
        <div class="mb-2"><label class="form-label">Product Line</label><input class="form-control" name="product_line" value="OTHER"></div>
        <div class="mb-2"><label class="form-label">Segment</label><input class="form-control" name="segment" value="ALL"></div>
        <div class="mb-2"><label class="form-label">Licensing Program</label><input class="form-control" name="licensing_program" value="CSP_NCE"></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary btn-sm">Save</button></div>
    </form>
</div></div></div>
@endsection
