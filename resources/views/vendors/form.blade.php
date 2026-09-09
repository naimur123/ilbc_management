@extends('layouts.app')
@section('title', $vendor->exists ? 'Edit Vendor' : 'Add Vendor')
@section('content')
<h4 class="mb-3">{{ $vendor->exists ? 'Edit Vendor' : 'Add Vendor' }}</h4>
<form action="{{ $vendor->exists ? route('vendors.update', $vendor) : route('vendors.store') }}" method="POST" class="kpi-card mb-3">
    @csrf @if($vendor->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-3"><label class="form-label">Vendor Code</label><input class="form-control" name="vendor_code" value="{{ old('vendor_code', $vendor->vendor_code) }}" placeholder="Auto if blank"></div>
        <div class="col-md-5"><label class="form-label">Name *</label><input class="form-control" name="name" value="{{ old('name', $vendor->name) }}" required></div>
        <div class="col-md-4"><label class="form-label">Type *</label>
            <select class="form-select" name="vendor_type">
                @foreach(['DISTRIBUTOR' => 'Distributor', 'CSP' => 'CSP', 'DIRECT_VENDOR' => 'Direct Vendor'] as $k => $l)
                <option value="{{ $k }}" {{ old('vendor_type', $vendor->vendor_type) === $k ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4"><label class="form-label">Contact Person</label><input class="form-control" name="contact_person" value="{{ old('contact_person', $vendor->contact_person) }}"></div>
        <div class="col-md-4"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone', $vendor->phone) }}"></div>
        <div class="col-md-4"><label class="form-label">Email</label><input class="form-control" name="email" value="{{ old('email', $vendor->email) }}"></div>
        <div class="col-12"><label class="form-label">Address</label><input class="form-control" name="address" value="{{ old('address', $vendor->address) }}"></div>
        <div class="col-md-3"><label class="form-label">Payment Terms</label>
            <select class="form-select" name="payment_terms_id"><option value="">-</option>
            @foreach($paymentTerms as $pt)<option value="{{ $pt->id }}" {{ old('payment_terms_id', $vendor->payment_terms_id) == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3"><label class="form-label">Credit Limit</label><input type="number" step="0.01" class="form-control" name="credit_limit" value="{{ old('credit_limit', $vendor->credit_limit) }}"></div>
        <div class="col-md-3"><label class="form-label">Tax/VAT No.</label><input class="form-control" name="tax_vat_number" value="{{ old('tax_vat_number', $vendor->tax_vat_number) }}"></div>
        <div class="col-md-3"><label class="form-label">Currency</label>
            <select class="form-select" name="currency_id"><option value="">-</option>
            @foreach($currencies as $c)<option value="{{ $c->id }}" {{ old('currency_id', $vendor->currency_id) == $c->id ? 'selected' : '' }}>{{ $c->code }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4"><label class="form-label">Lead Time (days)</label><input type="number" class="form-control" name="lead_time_days" value="{{ old('lead_time_days', $vendor->lead_time_days ?? 0) }}"></div>
        <div class="col-md-4"><label class="form-label">Status *</label>
            <select class="form-select" name="status">
                @foreach(['ACTIVE','INACTIVE','SUSPENDED'] as $s)<option value="{{ $s }}" {{ old('status', $vendor->status ?: 'ACTIVE') === $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach
            </select>
        </div>
        <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks">{{ old('remarks', $vendor->remarks) }}</textarea></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Save</button> <a href="{{ route('vendors.index') }}" class="btn btn-light">Cancel</a></div>
</form>

@can('vendor.api_manage')
@if($vendor->exists)
<div class="kpi-card">
    <h6><i class="bi bi-cloud-arrow-up-fill"></i> Provisioning Automation (Future API Integration)</h6>
    <p class="text-muted small">Off by default. Loading stays fully manual until you enable a live provider here and add valid credentials.</p>
    <form action="{{ route('vendors.provisioning.update', $vendor) }}" method="POST" class="row g-3">
        @csrf @method('PUT')
        @php($account = $vendor->provisioningAccount)
        <div class="col-md-3"><label class="form-label">Provider</label>
            <select class="form-select" name="provider">
                <option value="manual" {{ ($account->provider ?? 'manual') === 'manual' ? 'selected' : '' }}>Manual (no API)</option>
                <option value="partner_center" {{ ($account->provider ?? '') === 'partner_center' ? 'selected' : '' }}>Microsoft Partner Center</option>
                <option value="crayon" {{ ($account->provider ?? '') === 'crayon' ? 'selected' : '' }}>Crayon CloudIQ</option>
            </select>
        </div>
        <div class="col-md-3"><label class="form-label">Tenant / Account Ref</label><input class="form-control" name="tenant_id" value="{{ $account->tenant_id ?? '' }}"></div>
        <div class="col-md-3"><label class="form-label">Client ID</label><input class="form-control" name="client_id" placeholder="Leave blank to keep existing"></div>
        <div class="col-md-3"><label class="form-label">Client Secret</label><input type="password" class="form-control" name="client_secret" placeholder="Leave blank to keep existing"></div>
        <div class="col-md-3 form-check ms-3">
            <input type="checkbox" class="form-check-input" name="is_enabled" value="1" id="provEnabled" {{ ($account->is_enabled ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="provEnabled">Enabled</label>
        </div>
        <div class="col-md-3 form-check">
            <input type="checkbox" class="form-check-input" name="sandbox_mode" value="1" id="provSandbox" {{ ($account->sandbox_mode ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="provSandbox">Sandbox Mode</label>
        </div>
        <div class="col-12"><button class="btn btn-outline-primary btn-sm">Save Automation Settings</button></div>
    </form>
</div>
@endif
@endcan
@endsection
