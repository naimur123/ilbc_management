<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Solution Delivery & Acknowledgement Form</title>
    {{-- Clean Bootstrap 5 for standalone styling --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        .form-card {
            max-width: 900px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 40px;
        }
        .header-title {
            color: #0d6efd;
            letter-spacing: 0.5px;
        }
        .table-custom th {
            background-color: #f1f3f5;
            color: #495057;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-card">

        {{-- Form Header --}}
        <div class="text-center mb-4 border-bottom pb-3">
            <h2 class="fw-bold text-uppercase header-title mb-1">Customer Solution Delivery & Acknowledgement Form</h2>
            <p class="text-muted mb-0 fw-semibold">Solution / License Delivery Confirmation</p>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        {{-- 1. Read-Only Header Information --}}
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-custom align-middle">
                <tbody>
                    <tr>
                        <th style="width: 25%;">Customer Name</th>
                        <td>{{ $item->request->customer?->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Tenant Details</th>
                        <td>{{ $record?->tenant_account  ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Domain</th>
                        <td>{{ 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- 2. Read-Only Solution Details Table --}}
        <h5 class="fw-bold mb-3 text-secondary">Solution Details</h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th>Solution Name</th>
                        <th>Qty</th>
                        <th>Commitment</th>
                        <th>Billing Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Recurring</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td class="text-start">{{ $item->product?->name ?? $item->sku?->name ?? $placeholders['solution_name'] ?? 'N/A' }}</td>
                        <td>{{ $loadingRecord->actual_loaded_quantity ?? $item->quantity ?? $placeholders['quantity'] ?? 1 }}</td>
                        <td>{{ $loadingRecord->commitmentType?->name ?? $item->commitmentType?->name ?? $placeholders['commitment'] ?? 'N/A' }}</td>
                        <td>{{ $loadingRecord->billingType?->name ?? $item->billingType?->name ?? $placeholders['billing_type'] ?? 'N/A' }}</td>
                        <td>{{ isset($record->activation_date) ? $record->activation_date->format('d M, Y') : ($placeholders['start_date'] ?? 'N/A') }}</td>
                        <td>{{ isset($record->expiry_date) ? $record->expiry_date->format('d M, Y') : ($placeholders['end_date'] ?? 'N/A') }}</td>
                        <td>{{ ($item->is_recurring ?? false) ? 'Yes' : 'No' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($alreadyAcknowledged)
            <div class="alert alert-warning text-center">
                <h5 class="mb-1">You already acknowledged this record.</h5>
                <p class="mb-0">This acknowledgement link is unavailable now.</p>
            </div>
        @else
            {{-- 3. Customer Acknowledgement Area (Input Form) --}}
            <form action="{{ route('customer_ack.add') }}" method="POST">
            @csrf
            
            <div class="border rounded p-4 bg-light mb-4">
                <h5 class="fw-bold mb-2">Customer Acknowledgement</h5>
                <p class="text-muted small mb-4">
                    I acknowledge that we have received the above-mentioned licenses / solutions as per the description provided in this form and confirm that the information stated above is correct.
                </p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="signatory_name" class="form-label fw-bold">Signatory Person Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="signatory_name" name="signatory_name" value="{{ old('signatory_name') }}" required>
                        <input type="hidden" name="customer_id" value="{{ $customer_id }}">
                        <input type="hidden" name="request_item_id" value="{{ $item->id }}">
                        <input type="hidden" name="loading_record_id" value="{{ $record->id }}">
                    </div>

                    <div class="col-md-6">
                        <label for="designation" class="form-label fw-bold">Designation <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="designation" name="designation" value="{{ old('designation') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="acknowledgement_date" class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="acknowledgement_date" name="acknowledgement_date" value="{{ old('acknowledgement_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="signature" class="form-label fw-bold">Customer Signature / Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="signature" name="signature" placeholder="Type full name as digital signature" value="{{ old('signature') }}" required>
                    </div>
                </div>
            </div>

            {{-- Footer / Submit Action --}}
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                <span class="text-muted small">Thank you for choosing <strong>{{ config('app.company_name', 'Dhrubo Networks') }}</strong>.</span>
                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">Submit Acknowledgement</button>
            </div>
            </form>
        @endif

    </div>
</div>

</body>
</html>