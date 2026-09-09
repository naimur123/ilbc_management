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
                @foreach($customers as $c)<option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected':'' }}>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-3"><label class="form-label">Salesperson *</label>
            <select class="form-select" name="salesperson_id" required>
                <option value="">Select Salesperson</option>
                @foreach($salespersons as $s)<option value="{{ $s->id }}" {{ old('salesperson_id') == $s->id ? 'selected':'' }}>{{ $s->name }}</option>@endforeach
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
                    <th style="min-width:150px">Category</th><th style="min-width:170px">Product</th><th style="min-width:150px">SKU</th>
                    <th style="min-width:100px">Qty</th><th style="min-width:140px">Billing Type</th><th style="min-width:140px">Subscription</th>
                    <th style="min-width:120px">Start</th><th style="min-width:120px">End</th>
                    <th style="min-width:120px">Unit Price</th><th style="min-width:120px">Total</th><th></th>
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
        <div class="col-md-3"><label class="form-label">Advance %</label><input type="number" class="form-control" name="advance_percent" value="0"></div>
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
    $catalogData = $categories->map(fn ($c) => [
        'id' => $c->id, 'name' => $c->name,
        'products' => $c->products->map(fn ($p) => [
            'id' => $p->id, 'name' => $p->name,
            'skus' => $p->skus->map(fn ($s) => ['id' => $s->id, 'name' => $s->sku_code.($s->description ? ' - '.$s->description : '')])->values(),
        ])->values(),
    ])->values();
    $billingTypesData = $billingTypes->map(fn ($b) => ['id' => $b->id, 'name' => $b->name]);
    $subscriptionTypesData = $subscriptionTypes->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]);
@endphp
@push('scripts')
<script>
const CATALOG = @json($catalogData);
const BILLING_TYPES = @json($billingTypesData);
const SUBSCRIPTION_TYPES = @json($subscriptionTypesData);

let rowIndex = 0;

function optionsHtml(list, valueKey, labelKey) {
    return '<option value="">-</option>' + list.map(i => `<option value="${i[valueKey]}">${i[labelKey]}</option>`).join('');
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
        <td><select class="form-select form-select-sm" name="items[${i}][billing_type_id]">${optionsHtml(BILLING_TYPES,'id','name')}</select></td>
        <td><select class="form-select form-select-sm" name="items[${i}][subscription_type_id]">${optionsHtml(SUBSCRIPTION_TYPES,'id','name')}</select></td>
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

    const recalc = () => {
        const qty = parseFloat(tr.querySelector('.qty-input').value) || 0;
        const price = parseFloat(tr.querySelector('.price-input').value) || 0;
        tr.querySelector('.row-total').value = (qty * price).toFixed(2);
        recalcGrandTotal();
    };
    tr.querySelector('.qty-input').addEventListener('input', recalc);
    tr.querySelector('.price-input').addEventListener('input', recalc);
    tr.querySelector('.remove-row').addEventListener('click', () => { tr.remove(); recalcGrandTotal(); });
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
