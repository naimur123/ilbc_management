@extends('layouts.app')
@section('title', $user->exists ? 'Edit User' : 'Add User')
@section('content')
<h4 class="mb-3">{{ $user->exists ? 'Edit User' : 'Add User' }}</h4>
<form action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" method="POST" class="kpi-card">
    @csrf @if($user->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Employee Code</label><input class="form-control" name="employee_code" value="{{ old('employee_code', $user->employee_code) }}"></div>
        <div class="col-md-4"><label class="form-label">Name *</label><input class="form-control" name="name" value="{{ old('name', $user->name) }}" required></div>
        <div class="col-md-4"><label class="form-label">Email *</label><input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required></div>
        <div class="col-md-4"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone', $user->phone) }}"></div>
        <div class="col-md-4"><label class="form-label">Department</label>
            <select class="form-select" name="department_id"><option value="">-</option>
            @foreach($departments as $d)<option value="{{ $d->id }}" {{ old('department_id', $user->department_id) == $d->id ? 'selected':'' }}>{{ $d->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4 form-check pt-4">
            <input type="checkbox" class="form-check-input" name="is_active" value="1" id="isActive" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="isActive">Active</label>
        </div>
        <div class="col-md-6"><label class="form-label">Password {{ $user->exists ? '(leave blank to keep unchanged)' : '*' }}</label><input type="password" class="form-control" name="password" {{ $user->exists ? '' : 'required' }}></div>
        <div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" class="form-control" name="password_confirmation"></div>
        <div class="col-12">
            <label class="form-label">Roles</label><br>
            @foreach($roles as $role)
                <div class="form-check form-check-inline">
                    <input type="checkbox" class="form-check-input" name="roles[]" value="{{ $role->name }}" id="role{{ $role->id }}" {{ $user->roles->pluck('name')->contains($role->name) ? 'checked' : '' }}>
                    <label class="form-check-label" for="role{{ $role->id }}">{{ $role->name }}</label>
                </div>
            @endforeach
        </div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Save</button> <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a></div>
</form>
@endsection
