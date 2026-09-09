@extends('layouts.app')
@section('title', 'System Settings')
@section('content')
<h4 class="mb-3">System Settings</h4>
@php($get = fn($key, $default = '') => optional(($settings['general'] ?? collect())->firstWhere('key', $key))->value ?? $default)
<form action="{{ route('settings.update') }}" method="POST" class="kpi-card">
    @csrf @method('PUT')
    <h6 class="text-primary">General Settings</h6>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Company Name</label><input class="form-control" name="company_name" value="{{ $get('company_name', config('ilbc.company_name')) }}"></div>
        <div class="col-md-6"><label class="form-label">Address</label><input class="form-control" name="company_address" value="{{ $get('company_address') }}"></div>
        <div class="col-md-4"><label class="form-label">Phone</label><input class="form-control" name="company_phone" value="{{ $get('company_phone') }}"></div>
        <div class="col-md-4"><label class="form-label">Email</label><input type="email" class="form-control" name="company_email" value="{{ $get('company_email') }}"></div>
        <div class="col-md-4"></div>
        <div class="col-md-3"><label class="form-label">Currency Code</label><input class="form-control" name="currency_code" value="{{ $get('currency_code', config('ilbc.currency_code')) }}"></div>
        <div class="col-md-3"><label class="form-label">Currency Symbol</label><input class="form-control" name="currency_symbol" value="{{ $get('currency_symbol', config('ilbc.currency_symbol')) }}"></div>
        <div class="col-md-3"><label class="form-label">Timezone</label><input class="form-control" name="timezone" value="{{ $get('timezone', config('ilbc.timezone')) }}"></div>
        <div class="col-md-3"><label class="form-label">Date Format</label><input class="form-control" name="date_format" value="{{ $get('date_format', config('ilbc.date_format')) }}"></div>
    </div>

    <h6 class="text-primary mt-4">Financial Settings</h6>
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Minimum Margin %</label><input type="number" step="0.01" class="form-control" name="minimum_margin_percent" value="{{ $get('minimum_margin_percent', config('ilbc.minimum_margin_percent')) }}"></div>
        <div class="col-md-4"><label class="form-label">Department Head Approval Amount</label><input type="number" step="0.01" class="form-control" name="department_head_approval_amount" value="{{ $get('department_head_approval_amount', config('ilbc.department_head_approval_amount')) }}"></div>
    </div>

    <button class="btn btn-primary mt-3">Save</button>
</form>
@endsection
