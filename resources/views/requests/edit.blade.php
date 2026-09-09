@extends('layouts.app')
@section('title', 'Edit Request')
@section('content')
<h4 class="mb-3">Edit {{ $workRequest->request_no }}</h4>
<div class="alert alert-info small">Only header fields can be edited here. Product items are locked once a draft is saved — delete and recreate the request if items need to change before submission.</div>
<form action="{{ route('requests.update', $workRequest) }}" method="POST" class="kpi-card">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Work Order Number</label><input class="form-control" name="work_order_no" value="{{ old('work_order_no', $workRequest->work_order_no) }}"></div>
        <div class="col-md-4"><label class="form-label">PO Number</label><input class="form-control" name="po_number" value="{{ old('po_number', $workRequest->po_number) }}"></div>
        <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks">{{ old('remarks', $workRequest->salesEntry->remarks ?? '') }}</textarea></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Save</button> <a href="{{ route('requests.show', $workRequest) }}" class="btn btn-light">Cancel</a></div>
</form>
@endsection
