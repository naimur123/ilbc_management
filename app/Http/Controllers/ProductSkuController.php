<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Http\Request;

class ProductSkuController extends Controller
{
    public function index(Request $request)
    {
        $skus = ProductSku::with('product.category')
            ->when($request->product_id, fn ($q) => $q->where('product_id', $request->product_id))
            ->when($request->q, fn ($q) => $q->where('sku_code', 'like', "%{$request->q}%"))
            ->orderBy('sku_code')->paginate(30)->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('products.skus', compact('skus', 'products'));
    }

    public function store(Request $request)
    {
        ProductSku::create($this->validated($request));

        return back()->with('success', 'SKU added.');
    }

    public function update(Request $request, ProductSku $productSku)
    {
        $productSku->update($this->validated($request, $productSku->id));

        return back()->with('success', 'SKU updated.');
    }

    public function destroy(ProductSku $productSku)
    {
        $productSku->delete();

        return back()->with('success', 'SKU removed.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'sku_code' => 'required|string|max:60|unique:product_skus,sku_code,'.$ignoreId,
            'description' => 'nullable|string|max:255',
            'billing_model' => 'required|in:SEAT_BASED,CONSUMPTION,ONE_TIME',
            'consumption_unit' => 'nullable|string|max:30',
            'term' => 'nullable|in:MONTHLY,ANNUAL,TRIENNIAL,NA',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
