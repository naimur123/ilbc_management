<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\PaymentTerm;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $vendors = Vendor::when($request->q, fn ($q) => $q->where('name', 'like', "%{$request->q}%"))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('vendors.form', [
            'vendor' => new Vendor(),
            'currencies' => Currency::orderBy('code')->get(),
            'paymentTerms' => PaymentTerm::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['vendor_code'] = $data['vendor_code'] ?: $this->nextCode();
        $vendor = Vendor::create($data);
        $vendor->provisioningAccount()->create(['provider' => 'manual', 'is_enabled' => false]);

        return redirect()->route('vendors.index')->with('success', 'Vendor created.');
    }

    public function show(Vendor $vendor)
    {
        return redirect()->route('vendors.edit', $vendor);
    }

    public function edit(Vendor $vendor)
    {
        $vendor->loadMissing('provisioningAccount');

        return view('vendors.form', [
            'vendor' => $vendor,
            'currencies' => Currency::orderBy('code')->get(),
            'paymentTerms' => PaymentTerm::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Vendor $vendor)
    {
        $vendor->update($this->validated($request, $vendor->id));

        return redirect()->route('vendors.index')->with('success', 'Vendor updated.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        return back()->with('success', 'Vendor removed.');
    }

    public function performance()
    {
        $vendors = Vendor::withCount('productPrices as sku_count')->get()->map(function (Vendor $v) {
            $selections = \App\Models\VendorSelection::where('vendor_id', $v->id)->get();
            $v->selection_count = $selections->count();
            $v->lowest_cost_wins = $selections->where('is_lowest_cost_vendor', true)->count();
            $v->total_purchase_value = (float) $selections->sum('final_landed_cost');

            return $v;
        });

        return view('vendors.performance', compact('vendors'));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'vendor_code' => 'nullable|string|max:30|unique:vendors,vendor_code,'.$ignoreId,
            'name' => 'required|string|max:200',
            'vendor_type' => 'required|in:DISTRIBUTOR,CSP,DIRECT_VENDOR',
            'contact_person' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:255',
            'payment_terms_id' => 'nullable|exists:payment_terms,id',
            'credit_limit' => 'nullable|numeric|min:0',
            'tax_vat_number' => 'nullable|string|max:60',
            'currency_id' => 'nullable|exists:currencies,id',
            'lead_time_days' => 'nullable|integer|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE,SUSPENDED',
            'remarks' => 'nullable|string',
        ]);
    }

    private function nextCode(): string
    {
        $last = Vendor::orderByDesc('id')->first();
        $next = $last ? ((int) preg_replace('/\D/', '', $last->vendor_code) + 1) : 1;

        return 'VEN-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
