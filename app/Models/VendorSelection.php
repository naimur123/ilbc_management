<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorSelection extends Model
{
    protected $fillable = [
        'request_item_id', 'vendor_id', 'vendor_product_price_id', 'unit_cost', 'base_cost',
        'vat_amount', 'tax_amount', 'handling_cost', 'delivery_cost', 'other_cost',
        'final_landed_cost', 'gross_profit', 'gross_margin_percent',
        'is_lowest_cost_vendor', 'override_reason', 'selected_by',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2', 'base_cost' => 'decimal:2', 'vat_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2', 'handling_cost' => 'decimal:2', 'delivery_cost' => 'decimal:2',
        'other_cost' => 'decimal:2', 'final_landed_cost' => 'decimal:2', 'gross_profit' => 'decimal:2',
        'gross_margin_percent' => 'decimal:4', 'is_lowest_cost_vendor' => 'boolean',
    ];

    public function requestItem()
    {
        return $this->belongsTo(RequestItem::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function vendorProductPrice()
    {
        return $this->belongsTo(VendorProductPrice::class);
    }

    public function selectedBy()
    {
        return $this->belongsTo(User::class, 'selected_by');
    }
}
