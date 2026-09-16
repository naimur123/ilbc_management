<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesEntry extends Model
{
    protected $fillable = [
        'request_id', 'contact_person', 'mobile', 'email', 'source_lead', 'customer_type',
        'payment_terms_id', 'advance_amount', 'credit_days', 'billing_cycle', 'remarks',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_terms_id');
    }
}
