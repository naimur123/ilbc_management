<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoadingRecord extends Model
{
    protected $fillable = [
        'request_item_id', 'loading_date', 'loading_time', 'actual_loaded_quantity',
        'subscription_id', 'license_id', 'tenant_account', 'activation_date', 'expiry_date',
        'vendor_reference', 'distributor_reference', 'po_reference', 'technical_notes',
        'status', 'loaded_by', 'completed_at', 'commitment_type_id', 'billing_type_id', 'is_recurring', 'recurring_months'
    ];

    protected $casts = [
        'loading_date' => 'date', 'activation_date' => 'date', 'expiry_date' => 'date',
        'actual_loaded_quantity' => 'decimal:2', 'completed_at' => 'datetime',
    ];

    public function requestItem()
    {
        return $this->belongsTo(RequestItem::class);
    }

    public function attachments()
    {
        return $this->hasMany(LoadingAttachment::class);
    }

    public function billingType()
    {
        return $this->belongsTo(BillingType::class);
    }

    public function commitmentType()
    {
        return $this->belongsTo(CommitmentType::class);
    }

    public function checklists()
    {
        return $this->hasMany(LoadingChecklist::class);
    }

    public function auditRecord()
    {
        return $this->hasOne(AuditRecord::class);
    }

    public function loadedBy()
    {
        return $this->belongsTo(User::class, 'loaded_by');
    }
}
