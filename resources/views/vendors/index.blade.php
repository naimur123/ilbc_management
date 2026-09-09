@extends('layouts.app')
@section('title', 'Vendors')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Vendors</h4>
    @can('vendor.create')<a href="{{ route('vendors.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Vendor</a>@endcan
</div>
<div class="kpi-card">
    <form class="mb-3" method="GET"><input class="form-control form-control-sm w-25" name="q" placeholder="Search vendor..." value="{{ request('q') }}"></form>
    <div class="table-responsive">
    <table class="table table-sm align-middle">
        <thead><tr><th>Code</th><th>Name</th><th>Type</th><th>Phone</th><th>Lead Time</th><th>Status</th><th class="text-end">Action</th></tr></thead>
        <tbody>
        @foreach($vendors as $v)
        <tr>
            <td>{{ $v->vendor_code }}</td><td>{{ $v->name }}</td><td>{{ $v->vendor_type }}</td><td>{{ $v->phone }}</td><td>{{ $v->lead_time_days }}d</td>
            <td><span class="badge {{ $v->status === 'ACTIVE' ? 'bg-success' : ($v->status === 'SUSPENDED' ? 'bg-danger' : 'bg-secondary') }}">{{ $v->status }}</span></td>
            <td class="text-end">
                @can('vendor.edit')<a href="{{ route('vendors.edit', $v) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>@endcan
                @can('vendor.delete')
                <form action="{{ route('vendors.destroy', $v) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                @endcan
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    {{ $vendors->links() }}
</div>
@endsection
