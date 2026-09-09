<?php

namespace App\Http\Controllers\Lookups;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function index()
    {
        return view('master.taxes', ['items' => Tax::orderBy('name')->paginate(20)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:60',
            'type' => 'required|in:VAT,TAX,OTHER',
            'rate_percent' => 'required|numeric|min:0|max:100',
        ]);
        Tax::create($data + ['is_active' => true]);
        return back()->with('success', 'Tax added.');
    }

    public function update(Request $request, Tax $tax)
    {
        $data = $request->validate([
            'name' => 'required|string|max:60',
            'type' => 'required|in:VAT,TAX,OTHER',
            'rate_percent' => 'required|numeric|min:0|max:100',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $tax->update($data);
        return back()->with('success', 'Tax updated.');
    }

    public function destroy(Tax $tax)
    {
        $tax->delete();
        return back()->with('success', 'Tax removed.');
    }
}
