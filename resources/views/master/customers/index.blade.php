@extends('layouts.app')
@section('title', 'Customers')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Customers</h4>
    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Customer</a>
</div>
<div class="kpi-card">
    <form class="mb-3" method="GET"><input class="form-control form-control-sm w-25" name="q" placeholder="Search name/mobile..." value="{{ request('q') }}"></form>
    <div class="table-responsive">
    <table class="table table-sm align-middle">
        <thead><tr><th>Code</th><th>Name</th><th>Mobile</th><th>Department</th><th>Credit Limit</th><th>Outstanding</th><th>Status</th><th class="text-end">Action</th></tr></thead>
        <tbody>
        @foreach($customers as $c)
        <tr>
            <td>{{ $c->customer_code }}</td><td>{{ $c->name }}</td><td>{{ $c->mobile }}</td><td>{{ $c->department->name ?? '-' }}</td>
            <td>{{ number_format($c->credit_limit, 2) }}</td>
            <td class="{{ $c->isOverCreditLimit() ? 'text-danger fw-bold' : '' }}">{{ number_format($c->outstanding_balance, 2) }}</td>
            <td><span class="badge {{ $c->status === 'ACTIVE' ? 'bg-success' : ($c->status === 'BLOCKED' ? 'bg-danger' : 'bg-secondary') }}">{{ $c->status }}</span></td>
            <td class="text-end">
                <a href="{{ route('customers.edit', $c) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('customers.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this customer?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    {{ $customers->links() }}
</div>
@endsection
