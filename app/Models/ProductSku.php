<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductSku extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id', 'sku_code', 'microsoft_sku_id', 'microsoft_product_id', 'description',
        'billing_model', 'consumption_unit', 'term', 'min_seats', 'billing_cycle_options',
        'supports_trial', 'is_renewable', 'is_active',
    ];

    protected $casts = [
        'billing_cycle_options' => 'array',
        'supports_trial' => 'boolean',
        'is_renewable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function vendorPrices()
    {
        return $this->hasMany(VendorProductPrice::class);
    }

    public function currentVendorPrices()
    {
        return $this->hasMany(VendorProductPrice::class)->where('is_current', true);
    }

    public function isConsumptionBased(): bool
    {
        return $this->billing_model === 'CONSUMPTION';
    }
}
