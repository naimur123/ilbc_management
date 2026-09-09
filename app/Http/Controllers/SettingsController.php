<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        Gate::denyIf(! $request->user()->can('settings.manage'));

        $settings = SystemSetting::all()->groupBy('group');

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        Gate::denyIf(! $request->user()->can('settings.manage'));

        $data = $request->validate([
            'company_name' => 'nullable|string|max:150',
            'company_address' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:30',
            'company_email' => 'nullable|email|max:150',
            'currency_code' => 'nullable|string|max:10',
            'currency_symbol' => 'nullable|string|max:5',
            'timezone' => 'nullable|string|max:60',
            'date_format' => 'nullable|string|max:20',
            'minimum_margin_percent' => 'nullable|numeric|min:0|max:100',
            'department_head_approval_amount' => 'nullable|numeric|min:0',
        ]);

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }
            SystemSetting::updateOrCreate(
                ['group' => 'general', 'key' => $key],
                ['value' => (string) $value]
            );
        }

        return back()->with('success', 'Settings saved. Some changes require re-login to take full effect.');
    }
}
