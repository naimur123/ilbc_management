<?php

namespace App\Http\Controllers\Lookups;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        return view('master.currencies', ['items' => Currency::orderBy('code')->paginate(20)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:10|unique:currencies,code',
            'name' => 'required|string|max:60',
            'symbol' => 'required|string|max:10',
            'exchange_rate_to_base' => 'required|numeric|min:0',
        ]);
        Currency::create($data + ['is_active' => true]);
        return back()->with('success', 'Currency added.');
    }

    public function update(Request $request, Currency $currency)
    {
        $data = $request->validate([
            'code' => 'required|string|max:10|unique:currencies,code,'.$currency->id,
            'name' => 'required|string|max:60',
            'symbol' => 'required|string|max:10',
            'exchange_rate_to_base' => 'required|numeric|min:0',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $currency->update($data);
        return back()->with('success', 'Currency updated.');
    }

    public function destroy(Currency $currency)
    {
        $currency->delete();
        return back()->with('success', 'Currency removed.');
    }
}
