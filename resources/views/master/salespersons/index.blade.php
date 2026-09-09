@extends('layouts.app')
@section('title', 'Salespersons')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Salespersons</h4>
    <a href="{{ route('salespersons.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Salesperson</a>
</div>
<div class="kpi-card">
    <table class="table table-sm align-middle">
        <thead><tr><th>Code</th><th>Name</th><th>Department</th><th>Phone</th><th>Status</th><th class="text-end">Action</th></tr></thead>
        <tbody>
        @foreach($items as $s)
        <tr>
            <td>{{ $s->code }}</td><td>{{ $s->name }}</td><td>{{ $s->department->name ?? '-' }}</td><td>{{ $s->phone }}</td>
            <td>{!! $s->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
            <td class="text-end">
                <a href="{{ route('salespersons.edit', $s) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('salespersons.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    {{ $items->links() }}
</div>
@endsection
