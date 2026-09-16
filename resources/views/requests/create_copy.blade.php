@extends('layouts.app')
@section('title', 'Create Sales Entry')
@section('content')
    <h4 class="mb-3">Create New Request — Sales Entry</h4>

    <form action="{{ route('requests.store') }}" method="POST" enctype="multipart/form-data" id="requestForm">
    @csrf
    <div class="kpi-card mb-3">
        <h6 class="text-primary">Basic Information</h6>
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Customer *</label>
                <select class="form-select" name="customer_id" required>
                    <option value="">Select Customer</option>
                    @foreach($customers as $c)<option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Salesperson *</label>
                <select class="form-select" name="salesperson_id" required>
                    <option value="">Select Salesperson</option>
                    @foreach($salespersons as $s)<option value="{{ $s->id }}" {{ old('salesperson_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Department</label>
                <select class="form-select" name="department_id">
                    <option value="">-</option>
                    @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Customer Type</label>
                <input class="form-control" name="customer_type" placeholder="e.g. Corporate">
            </div>
            <div class="col-md-3"><label class="form-label">Contact Person</label><input class="form-control" name="contact_person"></div>
            <div class="col-md-3"><label class="form-label">Mobile</label><input class="form-control" name="mobile"></div>
            <div class="col-md-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email"></div>
            <div class="col-md-3"><label class="form-label">Source / Lead</label><input class="form-control" name="source_lead"></div>
        </div>
    </div>

    <div class="kpi-card mb-3">
        <h6 class="text-primary">Order Information</h6>
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Work Order Number</label><input class="form-control" name="work_order_no"></div>
            <div class="col-md-3"><label class="form-label">PO Number</label><input class="form-control" name="po_number"></div>
            <div class="col-md-3"><label class="form-label">Order Date</label><input type="date" class="form-control" name="order_date"></div>
            <div class="col-md-3"></div>
            <div class="col-md-4"><label class="form-label">Order Upload</label><input type="file" class="form-control" name="order_upload"></div>
            <div class="col-md-4"><label class="form-label">Quotation Upload</label><input type="file" class="form-control" name="quotation_upload"></div>
        </div>
    </div>

    <div class="kpi-card mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="text-primary mb-0">Product Information</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn"><i class="bi bi-plus-lg"></i> Add Product</button>
        </div>
        <div class="table-responsive mt-2">
            <table class="table table-sm align-middle" id="itemsTable">
                <thead class="table-light">
                    <tr>
                        <th style="min-width:150px">Category</th>
                        <th style="min-width:170px">Product</th>
                        <th style="min-width:150px">SKU</th>
                        <th style="min-width:100px">Qty</th>
                        <th style="min-width:140px">Commitment Type</th>
                        <th style="min-width:140px">Billing Type</th>
                        <th class="recurring-col-header" style="min-width:110px; display:none;">Is Recurring</th>
                        <th class="subscription-col-header" style="min-width:140px; display:none;">Subscription</th>
                        <th style="min-width:120px">Start</th>
                        <th style="min-width:120px">End</th>
                        <th style="min-width:120px">Unit Price</th>
                        <th style="min-width:120px">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
        <div class="text-end fw-bold">Grand Total: <span id="grandTotal">0.00</span> {{ config('ilbc.currency_symbol') }}</div>
    </div>

    <div class="kpi-card mb-3">
        <h6 class="text-primary">Payment Information</h6>
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Payment Terms</label>
                <select class="form-select" name="payment_terms_id">
                    <option value="">-</option>
                    @foreach($paymentTerms as $pt)<option value="{{ $pt->id }}">{{ $pt->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Advance (amount)</label><input type="number" class="form-control" name="advance_amount" value="0"></div>
            <div class="col-md-3"><label class="form-label">Credit Days</label><input type="number" class="form-control" name="credit_days" value="0"></div>
            <div class="col-md-3"><label class="form-label">Billing Cycle</label><input class="form-control" name="billing_cycle" placeholder="Monthly / Annual"></div>
            <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="2"></textarea></div>
        </div>
    </div>

    <div class="mb-4">
        <button type="submit" class="btn btn-outline-secondary" onclick="document.getElementById('submitAction').value='draft'">Save as Draft</button>
        <button type="submit" class="btn btn-primary" onclick="document.getElementById('submitAction').value='submit'">Submit for Billing Clearance</button>
        <a href="{{ route('requests.index') }}" class="btn btn-link">Cancel</a>
        <input type="hidden" name="submit_action" id="submitAction" value="draft">
    </div>
    </form>

    @php
    // Blade's @json(...) directive compiles by naively exploding its raw
    // argument text on every comma (Illuminate\View\Compilers\Concerns\
    // CompilesJson::compileJson()) — it is NOT comma/bracket-depth aware.
    // Passing a multi-key array literal (or a ->map() closure building one)
    // directly inside @json(...) truncates at the first comma and silently
    // drops the rest, producing malformed PHP with an unclosed '['. The fix
    // is to always build the value in a plain PHP variable first, then pass
    // only that single bare variable — with zero commas in its own text —
    // to @json().
    $catalogData = $categories->map(fn($c) => [
        'id' => $c->id,
        'name' => $c->name,
        'products' => $c->products->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'skus' => $p->skus->map(fn($s) => ['id' => $s->id, 'name' => $s->sku_code . ($s->description ? ' - ' . $s->description : '')])->values(),
        ])->values(),
    ])->values();
    $commitmentTypesData = $commitmentTypes->map(fn($b) => ['id' => $b->id, 'name' => $b->name]);
    $billingTypesData = $billingTypes->map(fn($b) => ['id' => $b->id, 'name' => $b->name]);
    $subscriptionTypesData = $subscriptionTypes->map(fn($s) => ['id' => $s->id, 'name' => $s->name]);
    @endphp
    @push('scripts')
      <script>
        const CATALOG = @json($catalogData);
        const COMMITMENT_TYPES = @json($commitmentTypesData);
        const BILLING_TYPES = @json($billingTypesData);
        const SUBSCRIPTION_TYPES = @json($subscriptionTypesData);

        let rowIndex = 0;

        function optionsHtml(list, valueKey, labelKey) {
            return '<option value="">-</option>' + list.map(i => `<option value="${i[valueKey]}">${i[labelKey]}</option>`).join('');
        }

        function addMonths(dateString, months) {
            if (!dateString) return '';
            const date = new Date(dateString + 'T00:00:00');
            if (Number.isNaN(date.getTime())) return '';
            const newDate = new Date(date);
            newDate.setMonth(newDate.getMonth() + months);
            return newDate.toISOString().slice(0, 10);
        }

        function getSelectedText(select) {
            if (!select) return '';
            const selectedIndex = select.selectedIndex;
            if (selectedIndex >= 0 && select.options[selectedIndex]) {
                return (select.options[selectedIndex].text || '').trim();
            }
            return '';
        }

        function updateRowEndDateFromCommitment(tr) {
            const startInput = tr.querySelector('input[name$="[start_date]"]');
            const endInput = tr.querySelector('input[name$="[end_date]"]');
            const commitmentSelect = tr.querySelector('select[name$="[commitment_type_id]"]');

            if (!startInput || !endInput || !commitmentSelect) return;

            const commitmentName = getSelectedText(commitmentSelect);
            if (!startInput.value) {
                endInput.value = '';
                return;
            }

            if (/annual/i.test(commitmentName)) {
                endInput.value = addMonths(startInput.value, 12);
                return;
            }

            if (/monthly/i.test(commitmentName)) {
                endInput.value = addMonths(startInput.value, 1);
            }
        }

        // Resets start and end dates whenever a configuration type (Billing or Commitment) changes
        function resetRowDates(tr) {
            const startInput = tr.querySelector('input[name$="[start_date]"]');
            const endInput = tr.querySelector('input[name$="[end_date]"]');
            if (startInput) startInput.value = '';
            if (endInput) endInput.value = '';
        }

        function isMonthlyBillingType(select) {
            if (!select) return false;
            const selectedValue = select.value;
            const selectedType = BILLING_TYPES.find(item => String(item.id) === String(selectedValue));
            const selectedName = (selectedType ? selectedType.name : (select.options[select.selectedIndex]?.text || '')).trim();
            return /monthly/i.test(selectedName);
        }

        function checkHasMonthlyRows() {
            return Array.from(document.querySelectorAll('.billing_type-select')).some(select => isMonthlyBillingType(select));
        }

        function updateTableColumnVisibility() {
            const showColumns = checkHasMonthlyRows();

            document.querySelectorAll('.recurring-col-header, .subscription-col-header').forEach(el => {
                el.style.display = showColumns ? '' : 'none';
            });

            document.querySelectorAll('#itemsBody tr').forEach(tr => {
                const recurringCell = tr.querySelector('.recurring-cell');
                const subscriptionCell = tr.querySelector('.subscription-cell');
                const billingTypeSelect = tr.querySelector('.billing_type-select');
                const recurringCheckbox = tr.querySelector('.recurring-checkbox');
                const recurringHidden = tr.querySelector('.recurring-hidden');

                if (recurringCell) recurringCell.style.display = showColumns ? '' : 'none';
                if (subscriptionCell) subscriptionCell.style.display = showColumns ? '' : 'none';

                const isMonthlyRow = isMonthlyBillingType(billingTypeSelect);
                if (recurringCheckbox && recurringHidden) {
                    recurringCheckbox.disabled = !isMonthlyRow;
                    recurringHidden.disabled = !isMonthlyRow;
                    
                    if (!isMonthlyRow) {
                        // Force uncheck and set value back to 0 when disabled
                        recurringCheckbox.checked = false;
                        recurringHidden.value = '0';
                    }
                }
            });
        }

        function addItemRow() {
            const i = rowIndex++;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><select class="form-select form-select-sm cat-select" name="items[${i}][product_category_id]" required>
                    <option value="">Select</option>${CATALOG.map(c => `<option value="${c.id}">${c.name}</option>`).join('')}</select></td>
                <td><select class="form-select form-select-sm prod-select" name="items[${i}][product_id]" required><option value="">Select category first</option></select></td>
                <td><select class="form-select form-select-sm sku-select" name="items[${i}][product_sku_id]" required><option value="">Select product first</option></select></td>
                <td><input type="number" min="0.01" step="0.01" class="form-control form-control-sm qty-input" name="items[${i}][quantity]" value="1" required></td>
                <td><select class="form-select form-select-sm" name="items[${i}][commitment_type_id]">${optionsHtml(COMMITMENT_TYPES,'id','name')}</select></td>
                <td><select class="form-select form-select-sm billing_type-select" name="items[${i}][billing_type_id]">${optionsHtml(BILLING_TYPES,'id','name')}</select></td>
                <td class="recurring-cell text-center align-middle" style="display:none;">
                    <!-- Only the hidden input has the 'name' attribute to ensure 0 or 1 is always submitted -->
                    <input type="hidden" class="recurring-hidden" name="items[${i}][is_recurring]" value="0">
                    <!-- The checkbox only updates the hidden input's value -->
                    <input type="checkbox" class="form-check-input recurring-checkbox" onchange="this.previousElementSibling.value = this.checked ? '1' : '0';">
                </td>
                <td class="subscription-cell" style="display:none;">
                    <select class="form-select form-select-sm" name="items[${i}][subscription_type_id]">${optionsHtml(SUBSCRIPTION_TYPES,'id','name')}</select>
                </td>
                <td><input type="date" class="form-control form-control-sm" name="items[${i}][start_date]"></td>
                <td><input type="date" class="form-control form-control-sm" name="items[${i}][end_date]"></td>
                <td><input type="number" min="0" step="0.01" class="form-control form-control-sm price-input" name="items[${i}][unit_selling_price]" value="0" required></td>
                <td><input type="text" class="form-control form-control-sm row-total" readonly value="0.00"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
            `;
            document.getElementById('itemsBody').appendChild(tr);

            const catSelect = tr.querySelector('.cat-select');
            const prodSelect = tr.querySelector('.prod-select');
            const skuSelect = tr.querySelector('.sku-select');
            const billingTypeSelect = tr.querySelector('.billing_type-select');
            const commitmentSelect = tr.querySelector('select[name$="[commitment_type_id]"]');
            const startInput = tr.querySelector('input[name$="[start_date]"]');

            catSelect.addEventListener('change', () => {
                const cat = CATALOG.find(c => c.id == catSelect.value);
                prodSelect.innerHTML = cat ? optionsHtml(cat.products, 'id', 'name') : '<option value="">Select category first</option>';
                skuSelect.innerHTML = '<option value="">Select product first</option>';
            });
            
            prodSelect.addEventListener('change', () => {
                const cat = CATALOG.find(c => c.id == catSelect.value);
                const prod = cat?.products.find(p => p.id == prodSelect.value);
                skuSelect.innerHTML = prod ? optionsHtml(prod.skus, 'id', 'name') : '<option value="">Select product first</option>';
            });
            
            // Changing Billing Type clears dates and updates recurring column visibility
            billingTypeSelect.addEventListener('change', () => {
                resetRowDates(tr);
                updateTableColumnVisibility();
            });

            // Changing Commitment Type clears dates and recalculates if start date is provided later
            commitmentSelect.addEventListener('change', () => {
                resetRowDates(tr);
            });

            // User inputs start date -> automatically calculate end date
            startInput.addEventListener('change', () => {
                updateRowEndDateFromCommitment(tr);
            });
            
            updateTableColumnVisibility();

            const recalc = () => {
                const qty = parseFloat(tr.querySelector('.qty-input').value) || 0;
                const price = parseFloat(tr.querySelector('.price-input').value) || 0;
                tr.querySelector('.row-total').value = (qty * price).toFixed(2);
                recalcGrandTotal();
            };
            tr.querySelector('.qty-input').addEventListener('input', recalc);
            tr.querySelector('.price-input').addEventListener('input', recalc);
            tr.querySelector('.remove-row').addEventListener('click', () => { 
                tr.remove(); 
                updateTableColumnVisibility(); 
                recalcGrandTotal(); 
            });
        }

        function recalcGrandTotal() {
            let total = 0;
            document.querySelectorAll('.row-total').forEach(el => total += parseFloat(el.value) || 0);
            document.getElementById('grandTotal').textContent = total.toFixed(2);
        }

        document.getElementById('addItemBtn').addEventListener('click', addItemRow);
        addItemRow();
      </script>
    @endpush
@endsection
