<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vendor_code', 'name', 'vendor_type', 'contact_person', 'phone', 'email', 'address',
        'payment_terms_id', 'credit_limit', 'tax_vat_number', 'currency_id',
        'lead_time_days', 'status', 'remarks',
    ];

    protected $casts = ['credit_limit' => 'decimal:2'];

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_terms_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function contacts()
    {
        return $this->hasMany(VendorContact::class);
    }

    public function productPrices()
    {
        return $this->hasMany(VendorProductPrice::class);
    }

    public function provisioningAccount()
    {
        return $this->hasOne(VendorProvisioningAccount::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }
}
