<?php

namespace App\Http\Controllers\Lookups;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionType;
use Illuminate\Http\Request;

class SubscriptionTypeController extends Controller
{
    public function index()
    {
        return view('master.subscription-types', ['items' => SubscriptionType::orderBy('name')->paginate(20)]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:60']);
        SubscriptionType::create($data + ['is_active' => true]);
        return back()->with('success', 'Subscription type added.');
    }

    public function update(Request $request, SubscriptionType $subscriptionType)
    {
        $data = $request->validate(['name' => 'required|string|max:60']);
        $data['is_active'] = $request->boolean('is_active');
        $subscriptionType->update($data);
        return back()->with('success', 'Subscription type updated.');
    }

    public function destroy(SubscriptionType $subscriptionType)
    {
        $subscriptionType->delete();
        return back()->with('success', 'Subscription type removed.');
    }
}
