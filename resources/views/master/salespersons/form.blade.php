@extends('layouts.app')
@section('title', $salesperson->exists ? 'Edit Salesperson' : 'Add Salesperson')
@section('content')
<h4 class="mb-3">{{ $salesperson->exists ? 'Edit Salesperson' : 'Add Salesperson' }}</h4>
<form action="{{ $salesperson->exists ? route('salespersons.update', $salesperson) : route('salespersons.store') }}" method="POST" class="kpi-card">
    @csrf @if($salesperson->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Name *</label><input class="form-control" name="name" value="{{ old('name', $salesperson->name) }}" required></div>
        <div class="col-md-4"><label class="form-label">Code *</label><input class="form-control" name="code" value="{{ old('code', $salesperson->code) }}" required></div>
        <div class="col-md-4"><label class="form-label">Linked User Account</label>
            <select class="form-select" name="user_id"><option value="">-</option>
            @foreach($users as $u)<option value="{{ $u->id }}" {{ old('user_id', $salesperson->user_id) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4"><label class="form-label">Department</label>
            <select class="form-select" name="department_id"><option value="">-</option>
            @foreach($departments as $d)<option value="{{ $d->id }}" {{ old('department_id', $salesperson->department_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone', $salesperson->phone) }}"></div>
        <div class="col-md-4"><label class="form-label">Email</label><input class="form-control" name="email" value="{{ old('email', $salesperson->email) }}"></div>
        <div class="col-md-4 d-flex align-items-end"><div class="form-check"><input type="checkbox" class="form-check-input" name="is_active" value="1" id="isActive" {{ old('is_active', $salesperson->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="isActive">Active</label></div></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Save</button> <a href="{{ route('salespersons.index') }}" class="btn btn-light">Cancel</a></div>
</form>
@endsection
