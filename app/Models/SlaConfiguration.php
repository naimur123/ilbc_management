<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Admin-defined SLA matching rule ("SLA Configuration" screen) — never
 * hard-coded. resolveConfiguration() in SlaMonitoringService picks the
 * most specific active row that matches a given request/item/priority.
 */
class SlaConfiguration extends Model
{
    protected $fillable = [
        'name', 'sla_type', 'product_id', 'product_category_id', 'customer_id', 'department_id',
        'priority', 'duration_minutes', 'reminder_before_minutes', 'escalate_after_minutes',
        'responsible_team', 'is_active',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'reminder_before_minutes' => 'integer',
        'escalate_after_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * How specific this rule is — used to pick the best match when more
     * than one active rule applies (a customer+product-specific rule
     * always wins over a generic one).
     */
    public function specificity(): int
    {
        return (int) ($this->product_id !== null) + (int) ($this->product_category_id !== null)
            + (int) ($this->customer_id !== null) + (int) ($this->department_id !== null)
            + (int) ($this->priority !== null);
    }
}
