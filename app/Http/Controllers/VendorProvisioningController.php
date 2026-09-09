<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorApiCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Gate;

/**
 * Admin-only screen backing the design addendum's Section 4.6: per-vendor
 * provisioning provider + sandbox toggle + credential entry. Credentials
 * are encrypted immediately and never redisplayed (Section 4.7).
 */
class VendorProvisioningController extends Controller
{
    public function update(Request $request, Vendor $vendor)
    {
        Gate::authorize('manageApi', $vendor);

        $data = $request->validate([
            'provider' => 'required|in:manual,partner_center,crayon',
            'is_enabled' => 'nullable|boolean',
            'sandbox_mode' => 'nullable|boolean',
            'tenant_id' => 'nullable|string|max:100',
            'client_id' => 'nullable|string|max:255',
            'client_secret' => 'nullable|string|max:255',
        ]);

        $account = $vendor->provisioningAccount()->updateOrCreate([], [
            'provider' => $data['provider'],
            'is_enabled' => $request->boolean('is_enabled'),
            'sandbox_mode' => $request->boolean('sandbox_mode', true),
            'tenant_id' => $data['tenant_id'] ?? null,
        ]);

        if ($request->filled('client_id') || $request->filled('client_secret')) {
            VendorApiCredential::create([
                'vendor_provisioning_account_id' => $account->id,
                'credential_type' => 'oauth_client',
                'encrypted_payload' => Crypt::encryptString(json_encode([
                    'client_id' => $data['client_id'] ?? null,
                    'client_secret' => $data['client_secret'] ?? null,
                ])),
                'created_by' => auth()->id(),
            ]);
        }

        return back()->with('success', 'Vendor automation settings saved.');
    }
}
