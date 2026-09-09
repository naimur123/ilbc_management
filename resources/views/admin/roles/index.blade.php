@extends('layouts.app')
@section('title', 'Roles')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Roles</h4>
    <a href="{{ route('roles.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Add Custom Role</a>
</div>
<div class="kpi-card p-0">
    <table class="table table-sm table-hover mb-0 align-middle">
        <thead class="table-light"><tr><th>Role</th><th>Users</th><th></th></tr></thead>
        <tbody>
        @forelse($roles as $r)
            <tr>
                <td>{{ $r->name }}</td><td>{{ $r->users_count }}</td>
                <td>
                    <a href="{{ route('roles.edit', $r) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i> Permissions</a>
                    @if(!in_array($r->name, ['Super Admin','Administrator']))
                    <form action="{{ route('roles.destroy', $r) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this role?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center text-muted py-4">No roles yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $roles->links() }}</div>
@endsection
