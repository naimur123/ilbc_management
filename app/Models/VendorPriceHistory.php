<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPriceHistory extends Model
{
    protected $table = 'vendor_price_history';

    public $timestamps = false;

    protected $fillable = [
        'vendor_product_price_id', 'vendor_id', 'product_sku_id',
        'old_unit_purchase_price', 'new_unit_purchase_price',
        'effective_from', 'effective_to', 'changed_by', 'changed_at',
    ];

    protected $casts = [
        'old_unit_purchase_price' => 'decimal:2',
        'new_unit_purchase_price' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'changed_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function sku()
    {
        return $this->belongsTo(ProductSku::class, 'product_sku_id');
    }
}
