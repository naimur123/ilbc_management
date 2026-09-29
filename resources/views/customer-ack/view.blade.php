<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Solution Delivery &amp; Acknowledgement Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            font-size: 0.8125rem; /* ~13px base font size */
        }

        .form-card {
            max-width: 950px;
            margin: 15px auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            padding: 20px 24px;
        }

        .header-title {
            color: #0d6efd;
            letter-spacing: 0.5px;
            font-size: 1.15rem;
        }

        .section-title {
            color: #495057;
            font-size: 0.875rem;
            border-left: 3px solid #0d6efd;
            padding-left: 8px;
        }

        /* Compact Table Styling */
        .table-custom th,
        .table-custom td {
            padding: 4px 10px;
            font-size: 0.775rem;
        }

        .table-custom th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
        }

        /* Compact Terms Box */
        .terms-wrapper {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            overflow: hidden;
            background-color: #fafafa;
        }

        .terms-header {
            background-color: #0d6efd;
            color: #ffffff;
            padding: 5px 12px;
        }

        .terms-header h6 {
            font-size: 0.8rem;
        }

        .terms-body {
            padding: 10px 14px;
        }

        .terms-list {
            padding-left: 18px;
            margin-bottom: 0;
            font-size: 0.725rem; /* Minimal size for Terms text */
            color: #555;
        }

        .terms-list li {
            padding-left: 2px;
            margin-bottom: 4px;
            line-height: 1.35;
        }

        .terms-list li:last-child {
            margin-bottom: 0;
        }

        /* Acceptance & Checkbox Box */
        .acceptance-box {
            background-color: #eef6ff;
            border: 1px solid #b6d4fe;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .acceptance-box .form-check-input {
            width: 1.05rem;
            height: 1.05rem;
            margin-top: 0.1rem;
            cursor: pointer;
        }

        .acceptance-box .form-check-label {
            padding-left: 6px;
            line-height: 1.3;
            font-size: 0.775rem;
            cursor: pointer;
        }

        .customer-company-name {
            color: #0d6efd;
        }

        .submit-button:disabled {
            cursor: not-allowed;
            opacity: .6;
        }

        .btn-compact {
            padding: 4px 16px;
            font-size: 0.8rem;
        }

        .alert-compact {
            padding: 6px 12px;
            font-size: 0.775rem;
            margin-bottom: 10px !important;
        }

        @media (max-width: 767.98px) {
            .form-card {
                margin: 10px;
                padding: 15px;
            }

            .header-title {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid px-2">
    <div class="form-card">

        {{-- FORM HEADER --}}
        <div class="text-center mb-2 border-bottom pb-2">
            <h5 class="fw-bold text-uppercase header-title mb-0">
                Customer Solution Delivery &amp; Acknowledgement Form
            </h5>
            <p class="text-muted mb-0 small" style="font-size: 0.75rem;">
                Solution / License Delivery Confirmation
            </p>
        </div>

        {{-- VALIDATION ERRORS --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-compact">
                <div class="fw-bold">Please correct the following:</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- SUCCESS MESSAGE --}}
        @if (session('success'))
            <div class="alert alert-success alert-compact">
                {{ session('success') }}
            </div>
        @endif

        {{-- WARNING MESSAGE --}}
        @if (session('warning'))
            <div class="alert alert-warning alert-compact">
                {{ session('warning') }}
            </div>
        @endif

        {{-- CUSTOMER INFORMATION --}}
        <h6 class="fw-bold section-title mb-1">Customer Information</h6>
        <div class="table-responsive mb-2">
            <table class="table table-bordered table-custom align-middle mb-0">
                <tbody>
                    <tr>
                        <th style="width: 20%;">Customer Name</th>
                        <td style="width: 30%;">{{ $item->request->customer?->name ?? 'N/A' }}</td>
                        <th style="width: 15%;">Tenant Details</th>
                        <td style="width: 35%;">{{ $record?->tenant_account ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Domain</th>
                        <td colspan="3">{{ $record?->domain ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- SOLUTION DETAILS --}}
        <h6 class="fw-bold section-title mb-1">Solution Details</h6>
        <div class="table-responsive mb-2">
            <table class="table table-bordered table-custom text-center align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 3%;">#</th>
                        <th class="text-start">Solution Name</th>
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
                        <td class="text-start">
                            {{ $item->product?->name ?? ($item->sku?->name ?? ($placeholders['solution_name'] ?? 'N/A')) }}
                        </td>
                        <td>
                            {{ $record?->actual_loaded_quantity ?? ($item->quantity ?? ($placeholders['quantity'] ?? 1)) }}
                        </td>
                        <td>
                            {{ $record?->commitmentType?->name ?? ($item->commitmentType?->name ?? ($placeholders['commitment'] ?? 'N/A')) }}
                        </td>
                        <td>
                            {{ $record?->billingType?->name ?? ($item->billingType?->name ?? ($placeholders['billing_type'] ?? 'N/A')) }}
                        </td>
                        <td>
                            @if ($record?->activation_date)
                                {{ $record->activation_date->format('d M, Y') }}
                            @else
                                {{ $placeholders['start_date'] ?? 'N/A' }}
                            @endif
                        </td>
                        <td>
                            @if ($record?->expiry_date)
                                {{ $record->expiry_date->format('d M, Y') }}
                            @else
                                {{ $placeholders['end_date'] ?? 'N/A' }}
                            @endif
                        </td>
                        <td>
                            {{ ($record?->is_recurring ?? ($item->is_recurring ?? false)) ? 'Yes' : 'No' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- TERMS & CONDITIONS --}}
        <div class="terms-wrapper mb-2">
            <div class="terms-header">
                <h6 class="fw-bold mb-0">Terms &amp; Conditions</h6>
            </div>
            <div class="terms-body">
                @if ($terms->count())
                    <ol class="terms-list">
                        @foreach ($terms as $term)
                            <li>
                                <strong>{{ $term->title }}:</strong> {{ $term->description }}
                            </li>
                        @endforeach
                    </ol>
                @else
                    <div class="alert alert-danger alert-compact mb-0">
                        Terms &amp; Conditions are currently unavailable. Please contact the service provider.
                    </div>
                @endif
            </div>
        </div>

        {{-- ALREADY ACKNOWLEDGED OR FORM --}}
        @if ($alreadyAcknowledged)
            <div class="alert alert-warning text-center alert-compact mb-0">
                <strong class="d-block">You already acknowledged this record.</strong>
                <span>This acknowledgement link is unavailable now.</span>
            </div>
        @else
            <form id="customerAcknowledgementForm" action="{{ url('/customer_ack/add') }}" method="POST">
                @csrf

                <input type="hidden" name="customer_id" value="{{ $customer_id }}">
                <input type="hidden" name="request_item_id" value="{{ $item->id }}">
                <input type="hidden" name="loading_record_id" value="{{ $record->id }}">

                {{-- TERMS ACCEPTANCE --}}
                <div class="acceptance-box mb-2">
                    <div class="form-check mb-0">
                        <input type="checkbox"
                               class="form-check-input @error('accept_all_terms') is-invalid @enderror"
                               id="accept_all_terms"
                               name="accept_all_terms"
                               value="1"
                               {{ old('accept_all_terms') ? 'checked' : '' }}
                               required>

                        <label class="form-check-label fw-semibold" for="accept_all_terms">
                            On behalf of <strong class="customer-company-name">{{ $item->request->customer?->name }}</strong>, {{ $declaration }}
                        </label>

                        @error('accept_all_terms')
                            <div class="invalid-feedback small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- FOOTER / SUBMIT --}}
                <div class="d-flex flex-row justify-content-between align-items-center">
                    <span class="text-muted" style="font-size: 0.725rem;">
                        Thank you for choosing <strong>{{ config('app.company_name', 'Dhrubo Networks') }}</strong>.
                    </span>

                    <button type="submit"
                            id="acknowledgementSubmitButton"
                            class="btn btn-primary btn-compact fw-bold submit-button"
                            disabled>
                        Submit Acknowledgement
                    </button>
                </div>
            </form>
        @endif

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('customerAcknowledgementForm');
        const termsCheckbox = document.getElementById('accept_all_terms');
        const submitButton = document.getElementById('acknowledgementSubmitButton');

        if (!form || !termsCheckbox || !submitButton) return;

        function updateSubmitButton() {
            submitButton.disabled = !termsCheckbox.checked;
        }

        termsCheckbox.addEventListener('change', updateSubmitButton);
        updateSubmitButton();

        form.addEventListener('submit', function (event) {
            if (!termsCheckbox.checked) {
                event.preventDefault();
                termsCheckbox.focus();
                return;
            }

            submitButton.disabled = true;
            submitButton.textContent = 'Submitting...';
        });
    });
</script>

</body>
</html>