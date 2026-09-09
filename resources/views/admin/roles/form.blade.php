@extends('layouts.app')
@section('title', $role->exists ? 'Edit Role' : 'Add Role')
@section('content')
<h4 class="mb-3">{{ $role->exists ? 'Edit Role: '.$role->name : 'Add Custom Role' }}</h4>
<form action="{{ $role->exists ? route('roles.update', $role) : route('roles.store') }}" method="POST" class="kpi-card">
    @csrf @if($role->exists) @method('PUT') @endif
    <div class="mb-3 col-md-6">
        <label class="form-label">Role Name *</label>
        <input class="form-control" name="name" value="{{ old('name', $role->name) }}" required {{ in_array($role->name, ['Super Admin','Administrator']) ? 'readonly' : '' }}>
    </div>
    <label class="form-label">Permissions</label>
    @php($rolePermissions = $role->exists ? $role->permissions->pluck('name')->all() : [])
    <div class="row">
    @foreach($permissions as $group => $perms)
        <div class="col-md-4 mb-3">
            <div class="fw-semibold text-uppercase small text-muted">{{ $group }}</div>
            @foreach($perms as $p)
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="permissions[]" value="{{ $p->name }}" id="perm{{ $p->id }}" {{ in_array($p->name, $rolePermissions) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="perm{{ $p->id }}">{{ $p->name }}</label>
                </div>
            @endforeach
        </div>
    @endforeach
    </div>
    <div class="mt-3"><button class="btn btn-primary">Save</button> <a href="{{ route('roles.index') }}" class="btn btn-light">Cancel</a></div>
</form>
@endsection
