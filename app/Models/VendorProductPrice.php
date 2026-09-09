<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProductPrice extends Model
{
    protected $fillable = [
        'vendor_id', 'product_id', 'product_sku_id', 'unit_purchase_price', 'cost_unit',
        'currency_id', 'vat_percent', 'tax_percent', 'other_cost', 'handling_cost',
        'delivery_cost', 'effective_from', 'effective_to', 'minimum_quantity',
        'price_type', 'is_current', 'remarks', 'created_by',
    ];

    protected $casts = [
        'unit_purchase_price' => 'decimal:2',
        'vat_percent' => 'decimal:3',
        'tax_percent' => 'decimal:3',
        'other_cost' => 'decimal:2',
        'handling_cost' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_current' => 'boolean',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sku()
    {
        return $this->belongsTo(ProductSku::class, 'product_sku_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
