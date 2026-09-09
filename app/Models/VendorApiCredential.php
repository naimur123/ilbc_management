<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorApiCredential extends Model
{
    protected $fillable = ['vendor_provisioning_account_id', 'credential_type', 'encrypted_payload', 'expires_at', 'created_by'];
    protected $casts = ['expires_at' => 'datetime'];
    protected $hidden = ['encrypted_payload'];

    public function account()
    {
        return $this->belongsTo(VendorProvisioningAccount::class, 'vendor_provisioning_account_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
