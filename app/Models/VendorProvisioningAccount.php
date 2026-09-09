<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorProvisioningAccount extends Model
{
    use SoftDeletes;

    protected $fillable = ['vendor_id', 'provider', 'tenant_id', 'is_enabled', 'sandbox_mode'];
    protected $casts = ['is_enabled' => 'boolean', 'sandbox_mode' => 'boolean'];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function credentials()
    {
        return $this->hasMany(VendorApiCredential::class);
    }
}
