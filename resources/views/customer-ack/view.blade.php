<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Customer Solution Delivery &amp; Acknowledgement Form
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        .form-card {
            max-width: 1000px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 10px;
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

        .section-title {
            color: #495057;
        }

        .terms-wrapper {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }

        .terms-header {
            background-color: #0d6efd;
            color: #ffffff;
            padding: 15px 20px;
        }

        .terms-body {
            padding: 25px;
        }

        .terms-list {
            padding-left: 24px;
            margin-bottom: 0;
        }

        .terms-list li {
            padding-left: 5px;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .terms-list li:last-child {
            margin-bottom: 0;
        }

        .customer-declaration {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 18px;
            border-radius: 5px;
        }

        .acceptance-box {
            background-color: #eef6ff;
            border: 1px solid #b6d4fe;
            border-radius: 8px;
            padding: 20px;
        }

        .acceptance-box .form-check-input {
            width: 1.3rem;
            height: 1.3rem;
            margin-top: 0.25rem;
            cursor: pointer;
        }

        .acceptance-box .form-check-label {
            padding-left: 8px;
            line-height: 1.6;
            cursor: pointer;
        }

        .customer-company-name {
            color: #0d6efd;
        }

        .submit-button:disabled {
            cursor: not-allowed;
            opacity: .6;
        }

        @media (max-width: 767.98px) {

            .form-card {
                margin: 15px auto;
                padding: 20px;
            }

            .header-title {
                font-size: 1.4rem;
            }

            .terms-body {
                padding: 18px;
            }

        }
    </style>

</head>

<body>

<div class="container">

    <div class="form-card">


        {{-- ===================================================== --}}
        {{-- FORM HEADER --}}
        {{-- ===================================================== --}}

        <div class="text-center mb-4 border-bottom pb-3">

            <h2 class="fw-bold text-uppercase header-title mb-1">

                Customer Solution Delivery
                &amp;
                Acknowledgement Form

            </h2>

            <p class="text-muted mb-0 fw-semibold">

                Solution / License Delivery Confirmation

            </p>

        </div>


        {{-- ===================================================== --}}
        {{-- VALIDATION ERRORS --}}
        {{-- ===================================================== --}}

        @if ($errors->any())

            <div class="alert alert-danger mb-4">

                <div class="fw-bold mb-2">

                    Please correct the following:

                </div>

                <ul class="mb-0">

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- ===================================================== --}}
        {{-- SUCCESS MESSAGE --}}
        {{-- ===================================================== --}}

        @if (session('success'))

            <div class="alert alert-success mb-4">

                {{ session('success') }}

            </div>

        @endif


        {{-- ===================================================== --}}
        {{-- WARNING MESSAGE --}}
        {{-- ===================================================== --}}

        @if (session('warning'))

            <div class="alert alert-warning mb-4">

                {{ session('warning') }}

            </div>

        @endif


        {{-- ===================================================== --}}
        {{-- CUSTOMER INFORMATION --}}
        {{-- ===================================================== --}}

        <h5 class="fw-bold section-title mb-3">

            Customer Information

        </h5>


        <div class="table-responsive mb-4">

            <table
                class="table table-bordered table-custom align-middle">

                <tbody>

                    <tr>

                        <th style="width: 25%;">

                            Customer Name

                        </th>

                        <td>

                            {{
                                $item->request->customer?->name
                                ?? 'N/A'
                            }}

                        </td>

                    </tr>


                    <tr>

                        <th>

                            Tenant Details

                        </th>

                        <td>

                            {{
                                $record?->tenant_account
                                ?? 'N/A'
                            }}

                        </td>

                    </tr>


                    <tr>

                        <th>

                            Domain

                        </th>

                        <td>

                            {{
                                $record?->domain
                                ?? 'N/A'
                            }}

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>


        {{-- ===================================================== --}}
        {{-- SOLUTION DETAILS --}}
        {{-- ===================================================== --}}

        <h5 class="fw-bold section-title mb-3">

            Solution Details

        </h5>


        <div class="table-responsive mb-4">

            <table
                class="table table-bordered text-center align-middle">

                <thead class="table-dark">

                    <tr>

                        <th style="width: 5%;">
                            #
                        </th>

                        <th>
                            Solution Name
                        </th>

                        <th>
                            Qty
                        </th>

                        <th>
                            Commitment
                        </th>

                        <th>
                            Billing Type
                        </th>

                        <th>
                            Start Date
                        </th>

                        <th>
                            End Date
                        </th>

                        <th>
                            Recurring
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <tr>

                        <td>
                            1
                        </td>


                        <td class="text-start">

                            {{
                                $item->product?->name
                                ?? (
                                    $item->sku?->name
                                    ?? (
                                        $placeholders['solution_name']
                                        ?? 'N/A'
                                    )
                                )
                            }}

                        </td>


                        <td>

                            {{
                                $record?->actual_loaded_quantity
                                ?? (
                                    $item->quantity
                                    ?? (
                                        $placeholders['quantity']
                                        ?? 1
                                    )
                                )
                            }}

                        </td>


                        <td>

                            {{
                                $record?->commitmentType?->name
                                ?? (
                                    $item->commitmentType?->name
                                    ?? (
                                        $placeholders['commitment']
                                        ?? 'N/A'
                                    )
                                )
                            }}

                        </td>


                        <td>

                            {{
                                $record?->billingType?->name
                                ?? (
                                    $item->billingType?->name
                                    ?? (
                                        $placeholders['billing_type']
                                        ?? 'N/A'
                                    )
                                )
                            }}

                        </td>


                        <td>

                            @if ($record?->activation_date)

                                {{
                                    $record
                                        ->activation_date
                                        ->format('d M, Y')
                                }}

                            @else

                                {{
                                    $placeholders['start_date']
                                    ?? 'N/A'
                                }}

                            @endif

                        </td>


                        <td>

                            @if ($record?->expiry_date)

                                {{
                                    $record
                                        ->expiry_date
                                        ->format('d M, Y')
                                }}

                            @else

                                {{
                                    $placeholders['end_date']
                                    ?? 'N/A'
                                }}

                            @endif

                        </td>


                        <td>

                            {{
                                (
                                    $record?->is_recurring
                                    ?? $item->is_recurring
                                    ?? false
                                )
                                    ? 'Yes'
                                    : 'No'
                            }}

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>


        {{-- ===================================================== --}}
        {{-- TERMS & CONDITIONS --}}
        {{-- ===================================================== --}}

        <div class="terms-wrapper mb-4">

            <div class="terms-header">

                <h5 class="fw-bold mb-0">

                    Terms &amp; Conditions

                </h5>

            </div>


            <div class="terms-body">

                @if ($terms->count())

                    <ol class="terms-list">

                        @foreach ($terms as $term)

                            <li>

                                <strong>

                                    {{ $term->title }}:

                                </strong>

                                {{ $term->description }}

                            </li>

                        @endforeach

                    </ol>

                @else

                    <div class="alert alert-danger mb-0">

                        Terms &amp; Conditions are currently
                        unavailable.

                        Please contact the service provider.

                    </div>

                @endif


                {{-- ============================================= --}}
                {{-- CUSTOMER DECLARATION --}}
                {{-- ============================================= --}}

                <div class="customer-declaration mt-4">

                    <h6 class="fw-bold mb-2">

                        Customer Declaration

                    </h6>

                    <p class="mb-0">

                        {{ $declaration }}

                    </p>

                </div>

            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- ALREADY ACKNOWLEDGED --}}
        {{-- ===================================================== --}}

        @if ($alreadyAcknowledged)

            <div class="alert alert-warning text-center">

                <h5 class="mb-1">

                    You already acknowledged this record.

                </h5>

                <p class="mb-0">

                    This acknowledgement link is unavailable now.

                </p>

            </div>

        @else


            {{-- ================================================= --}}
            {{-- ACKNOWLEDGEMENT FORM --}}
            {{-- ================================================= --}}

            <form
                id="customerAcknowledgementForm"
                action="{{ url('/customer_ack/add') }}"
                method="POST">

                @csrf


                {{-- ============================================= --}}
                {{-- IDENTIFIERS --}}
                {{-- ============================================= --}}

                <input
                    type="hidden"
                    name="customer_id"
                    value="{{ $customer_id }}">


                <input
                    type="hidden"
                    name="request_item_id"
                    value="{{ $item->id }}">


                <input
                    type="hidden"
                    name="loading_record_id"
                    value="{{ $record->id }}">


                {{-- ============================================= --}}
                {{-- TERMS ACCEPTANCE --}}
                {{-- ============================================= --}}

                <div class="acceptance-box mb-4">

                    <div class="form-check">

                        <input
                            type="checkbox"
                            class="form-check-input @error('accept_all_terms') is-invalid @enderror"
                            id="accept_all_terms"
                            name="accept_all_terms"
                            value="1"
                            {{ old('accept_all_terms') ? 'checked' : '' }}
                            required>


                        <label
                            class="form-check-label fw-semibold"
                            for="accept_all_terms">

                            On behalf of

                            <strong class="customer-company-name">

                                {{
                                    $item->request->customer?->name
                                    ?? 'the customer company'
                                }}

                            </strong>,

                            I confirm that I have reviewed,
                            understood, and accepted all of the
                            Terms &amp; Conditions and the Customer
                            Declaration stated above.

                        </label>


                        @error('accept_all_terms')

                            <div class="invalid-feedback">

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                </div>


                {{-- ============================================= --}}
                {{-- FOOTER / SUBMIT --}}
                {{-- ============================================= --}}

                <div
                    class="d-flex flex-column flex-sm-row
                           justify-content-between
                           align-items-center gap-3">

                    <span class="text-muted small">

                        Thank you for choosing

                        <strong>

                            {{
                                config(
                                    'app.company_name',
                                    'Dhrubo Networks'
                                )
                            }}

                        </strong>.

                    </span>


                    <button
                        type="submit"
                        id="acknowledgementSubmitButton"
                        class="btn btn-primary
                               px-4 py-2 fw-bold
                               submit-button"
                        disabled>

                        Submit Acknowledgement

                    </button>

                </div>

            </form>

        @endif


    </div>

</div>


{{-- ========================================================= --}}
{{-- FORM JAVASCRIPT --}}
{{-- ========================================================= --}}

<script>

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const form =
                document.getElementById(
                    'customerAcknowledgementForm'
                );

            const termsCheckbox =
                document.getElementById(
                    'accept_all_terms'
                );

            const submitButton =
                document.getElementById(
                    'acknowledgementSubmitButton'
                );


            if (
                !form ||
                !termsCheckbox ||
                !submitButton
            ) {
                return;
            }


            function updateSubmitButton() {

                submitButton.disabled =
                    !termsCheckbox.checked;

            }


            termsCheckbox.addEventListener(
                'change',
                function () {

                    updateSubmitButton();

                }
            );


            updateSubmitButton();


            form.addEventListener(
                'submit',
                function (event) {

                    if (!termsCheckbox.checked) {

                        event.preventDefault();

                        termsCheckbox.focus();

                        return;

                    }


                    submitButton.disabled = true;

                    submitButton.textContent =
                        'Submitting...';

                }
            );

        }
    );

</script>

</body>

</html>