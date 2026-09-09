<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        return view('products.categories', ['items' => ProductCategory::orderBy('sort_order')->orderBy('name')->paginate(30)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:30|unique:product_categories,code',
            'publisher' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);
        ProductCategory::create($data + ['is_active' => true, 'publisher' => $data['publisher'] ?: 'Microsoft']);

        return back()->with('success', 'Category added.');
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:30|unique:product_categories,code,'.$productCategory->id,
            'publisher' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $productCategory->update($data);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        $productCategory->delete();

        return back()->with('success', 'Category removed.');
    }
}
