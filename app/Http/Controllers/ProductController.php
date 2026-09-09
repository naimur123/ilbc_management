<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->category_id, fn ($q) => $q->where('product_category_id', $request->category_id))
            ->orderBy('name')->paginate(25)->withQueryString();
        $categories = ProductCategory::orderBy('name')->get();

        return view('products.products', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Product::create($data);

        return back()->with('success', 'Product added.');
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request));

        return back()->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return back()->with('success', 'Product removed.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'product_category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:200',
            'product_line' => 'required|string|max:30',
            'segment' => 'required|string|max:30',
            'licensing_program' => 'required|string|max:30',
            'description' => 'nullable|string',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
