@extends('layouts.app')
@section('title', 'User & Role Management')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Users</h4>
    <div>
        <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-secondary">Manage Roles</a>
        <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Add User</a>
    </div>
</div>
<form class="kpi-card mb-3 d-flex gap-2" method="GET">
    <input class="form-control form-control-sm" type="search" name="q" value="{{ request('q') }}" placeholder="Search name/email" style="max-width:280px;">
    <button class="btn btn-sm btn-outline-secondary">Search</button>
</form>
<div class="kpi-card p-0">
    <table class="table table-sm table-hover mb-0 align-middle">
        <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Department</th><th>Roles</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($users as $u)
            <tr>
                <td>{{ $u->name }}</td><td>{{ $u->email }}</td><td>{{ $u->department->name ?? '-' }}</td>
                <td>@foreach($u->roles as $r)<span class="badge bg-light text-dark border">{{ $r->name }}</span>@endforeach</td>
                <td>{!! $u->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                <td>
                    <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                    <form action="{{ route('users.destroy', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Deactivate this user?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
