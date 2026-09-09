@extends('layouts.app')
@section('title', $customer->exists ? 'Edit Customer' : 'Add Customer')
@section('content')
<h4 class="mb-3">{{ $customer->exists ? 'Edit Customer' : 'Add Customer' }}</h4>
<form action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}" method="POST" class="kpi-card">
    @csrf @if($customer->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Customer Code</label><input class="form-control" name="customer_code" value="{{ old('customer_code', $customer->customer_code) }}" placeholder="Auto if left blank"></div>
        <div class="col-md-4"><label class="form-label">Name *</label><input class="form-control" name="name" value="{{ old('name', $customer->name) }}" required></div>
        <div class="col-md-4"><label class="form-label">Contact Person</label><input class="form-control" name="contact_person" value="{{ old('contact_person', $customer->contact_person) }}"></div>
        <div class="col-md-4"><label class="form-label">Mobile</label><input class="form-control" name="mobile" value="{{ old('mobile', $customer->mobile) }}"></div>
        <div class="col-md-4"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ old('email', $customer->email) }}"></div>
        <div class="col-md-4"><label class="form-label">Department</label>
            <select class="form-select" name="department_id"><option value="">-</option>
            @foreach($departments as $d)<option value="{{ $d->id }}" {{ old('department_id', $customer->department_id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-8"><label class="form-label">Address</label><input class="form-control" name="address" value="{{ old('address', $customer->address) }}"></div>
        <div class="col-md-4"><label class="form-label">Source / Lead</label><input class="form-control" name="source" value="{{ old('source', $customer->source) }}"></div>
        <div class="col-md-4"><label class="form-label">Customer Type</label><input class="form-control" name="customer_type" value="{{ old('customer_type', $customer->customer_type) }}"></div>
        <div class="col-md-4"><label class="form-label">Payment Terms</label>
            <select class="form-select" name="payment_terms_id"><option value="">-</option>
            @foreach($paymentTerms as $pt)<option value="{{ $pt->id }}" {{ old('payment_terms_id', $customer->payment_terms_id) == $pt->id ? 'selected' : '' }}>{{ $pt->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4"><label class="form-label">Credit Limit</label><input type="number" step="0.01" class="form-control" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}"></div>
        <div class="col-md-4"><label class="form-label">Status</label>
            <select class="form-select" name="status">
                @foreach(['ACTIVE','INACTIVE','BLOCKED'] as $s)<option value="{{ $s }}" {{ old('status', $customer->status ?: 'ACTIVE') === $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach
            </select>
        </div>
        <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks">{{ old('remarks', $customer->remarks) }}</textarea></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Save</button> <a href="{{ route('customers.index') }}" class="btn btn-light">Cancel</a></div>
</form>
@endsection
