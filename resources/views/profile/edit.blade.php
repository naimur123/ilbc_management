@extends('layouts.app')
@section('title', 'My Profile')
@section('content')
<h4 class="mb-3">My Profile</h4>
<form action="{{ route('profile.update') }}" method="POST" class="kpi-card" style="max-width:500px;">
    @csrf @method('PUT')
    <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name" value="{{ old('name', $user->name) }}" required></div>
    <div class="mb-3"><label class="form-label">Email</label><input class="form-control" value="{{ $user->email }}" disabled></div>
    <div class="mb-3"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone', $user->phone) }}"></div>
    <div class="mb-3"><label class="form-label">New Password</label><input type="password" class="form-control" name="password" placeholder="Leave blank to keep unchanged"></div>
    <div class="mb-3"><label class="form-label">Confirm Password</label><input type="password" class="form-control" name="password_confirmation"></div>
    <button class="btn btn-primary">Save</button>
</form>
@endsection
