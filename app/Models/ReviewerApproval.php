<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewerApproval extends Model
{
    protected $fillable = [
        'request_id', 'snapshot_total_selling_price', 'snapshot_total_vendor_cost',
        'snapshot_total_tax', 'snapshot_total_other_cost', 'snapshot_final_landed_cost',
        'snapshot_gross_profit', 'snapshot_gross_margin_percent', 'loading_source',
        'loading_source_vendor_id', 'special_instructions', 'tenant_account',
        'low_margin_approval_required', 'management_approval_obtained', 'decision',
        'remarks', 'decided_by', 'decided_at',
    ];

    protected $casts = [
        'snapshot_total_selling_price' => 'decimal:2',
        'snapshot_total_vendor_cost' => 'decimal:2',
        'snapshot_total_tax' => 'decimal:2',
        'snapshot_total_other_cost' => 'decimal:2',
        'snapshot_final_landed_cost' => 'decimal:2',
        'snapshot_gross_profit' => 'decimal:2',
        'snapshot_gross_margin_percent' => 'decimal:4',
        'low_margin_approval_required' => 'boolean',
        'management_approval_obtained' => 'boolean',
        'decided_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function checklists()
    {
        return $this->hasMany(ReviewerChecklist::class);
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /**
     * The vendor selected as "Loading Source" — which vendor will perform
     * the loading/installation once the request is approved. See migration
     * 2026_01_01_000160 for why `loading_source` (text) is kept alongside
     * this foreign key.
     */
    public function loadingSourceVendor()
    {
        return $this->belongsTo(Vendor::class, 'loading_source_vendor_id');
    }

    public function allMandatoryChecked(): bool
    {
        return $this->checklists()->where('is_mandatory', true)->where('is_checked', false)->doesntExist();
    }
}
