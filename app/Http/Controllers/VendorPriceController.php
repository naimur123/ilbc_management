<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ProductSku;
use App\Models\Vendor;
use App\Models\VendorPriceHistory;
use App\Models\VendorProductPrice;
use App\Services\VendorPriceService;
use Illuminate\Http\Request;

class VendorPriceController extends Controller
{
    public function index(Request $request)
    {
        $prices = VendorProductPrice::with(['vendor', 'sku.product', 'currency'])
            ->where('is_current', true)
            ->when($request->sku_id, fn ($q) => $q->where('product_sku_id', $request->sku_id))
            ->when($request->vendor_id, fn ($q) => $q->where('vendor_id', $request->vendor_id))
            ->orderBy('product_sku_id')
            ->paginate(25)->withQueryString();

        $vendors = Vendor::where('status', 'ACTIVE')->orderBy('name')->get();

        return view('vendor-prices.index', compact('prices', 'vendors'));
    }

    public function create()
    {
        return view('vendor-prices.create', [
            'vendors' => Vendor::where('status', 'ACTIVE')->orderBy('name')->get(),
            'skus' => ProductSku::with('product')->orderBy('sku_code')->get(),
            'currencies' => Currency::orderBy('code')->get(),
        ]);
    }

    public function store(Request $request, VendorPriceService $service)
    {
        $data = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'product_sku_id' => 'required|exists:product_skus,id',
            'unit_purchase_price' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
            'vat_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'other_cost' => 'nullable|numeric|min:0',
            'handling_cost' => 'nullable|numeric|min:0',
            'delivery_cost' => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
            'minimum_quantity' => 'nullable|integer|min:1',
            'price_type' => 'required|in:LIST,PARTNER_DISCOUNTED,PROMO,CUSTOM_QUOTE',
            'remarks' => 'nullable|string',
        ]);

        $vendor = Vendor::findOrFail($data['vendor_id']);
        $sku = ProductSku::findOrFail($data['product_sku_id']);
        $service->setPrice($vendor, $sku, $data);

        return redirect()->route('vendor-prices.index')->with('success', 'Vendor price saved. Previous price kept in history.');
    }

    public function history(Request $request)
    {
        $history = VendorPriceHistory::with(['vendor', 'sku.product'])
            ->when($request->sku_id, fn ($q) => $q->where('product_sku_id', $request->sku_id))
            ->latest('changed_at')->paginate(30)->withQueryString();

        return view('vendor-prices.history', compact('history'));
    }
}
