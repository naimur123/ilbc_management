<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingClearance extends Model
{
    protected $fillable = [
        'request_id', 'customer_outstanding', 'has_previous_unpaid_invoice', 'credit_limit_at_check',
        'advance_payment_required', 'security_deposit_required', 'special_approval_required',
        'decision', 'remarks', 'decided_by', 'decided_at',
    ];

    protected $casts = [
        'customer_outstanding' => 'decimal:2',
        'credit_limit_at_check' => 'decimal:2',
        'has_previous_unpaid_invoice' => 'boolean',
        'advance_payment_required' => 'boolean',
        'security_deposit_required' => 'boolean',
        'special_approval_required' => 'boolean',
        'decided_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
