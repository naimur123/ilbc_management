<?php

namespace App\Http\Controllers\Lookups;

use App\Http\Controllers\Controller;
use App\Models\PaymentTerm;
use Illuminate\Http\Request;

class PaymentTermController extends Controller
{
    public function index()
    {
        return view('master.payment-terms', ['items' => PaymentTerm::orderBy('name')->paginate(20)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'advance_percent' => 'required|integer|min:0|max:100',
            'credit_days' => 'required|integer|min:0|max:365',
        ]);
        PaymentTerm::create($data + ['is_active' => true]);
        return back()->with('success', 'Payment term added.');
    }

    public function update(Request $request, PaymentTerm $paymentTerm)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'advance_percent' => 'required|integer|min:0|max:100',
            'credit_days' => 'required|integer|min:0|max:365',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $paymentTerm->update($data);
        return back()->with('success', 'Payment term updated.');
    }

    public function destroy(PaymentTerm $paymentTerm)
    {
        $paymentTerm->delete();
        return back()->with('success', 'Payment term removed.');
    }
}
