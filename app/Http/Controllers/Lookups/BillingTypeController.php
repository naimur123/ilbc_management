<?php

namespace App\Http\Controllers\Lookups;

use App\Http\Controllers\Controller;
use App\Models\BillingType;
use Illuminate\Http\Request;

class BillingTypeController extends Controller
{
    public function index()
    {
        return view('master.billing-types', ['items' => BillingType::orderBy('name')->paginate(20)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:60']);
        BillingType::create($data + ['is_active' => true]);
        return back()->with('success', 'Billing type added.');
    }

    public function update(Request $request, BillingType $billingType)
    {
        $data = $request->validate(['name' => 'required|string|max:60']);
        $data['is_active'] = $request->boolean('is_active');
        $billingType->update($data);
        return back()->with('success', 'Billing type updated.');
    }

    public function destroy(BillingType $billingType)
    {
        $billingType->delete();
        return back()->with('success', 'Billing type removed.');
    }
}
