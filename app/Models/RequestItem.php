<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'request_id', 'product_category_id', 'product_id', 'product_sku_id', 'description',
        'quantity', 'commitment_type_id', 'billing_type_id', 'is_recurring', 'recurring_months', 'subscription_type_id', 'start_date', 'end_date',
        'unit_selling_price', 'total_selling_price', 'status',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_selling_price' => 'decimal:2',
        'total_selling_price' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (RequestItem $item) {
            $item->total_selling_price = round((float) $item->quantity * (float) $item->unit_selling_price, 2);
        });
    }

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sku()
    {
        return $this->belongsTo(ProductSku::class, 'product_sku_id');
    }
    public function billingType()
    {
        return $this->belongsTo(BillingType::class);
    }

    public function commitmentType()
    {
        return $this->belongsTo(CommitmentType::class);
    }


    public function subscriptionType()
    {
        return $this->belongsTo(SubscriptionType::class);
    }

    public function vendorSelection()
    {
        return $this->hasOne(VendorSelection::class);
    }

    public function loadingRecord()
    {
        return $this->hasOne(LoadingRecord::class);
    }

    public function auditRecord()
    {
        return $this->hasOne(AuditRecord::class);
    }
}
