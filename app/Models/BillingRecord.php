<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingRecord extends Model
{
    protected $fillable = [
        'request_id', 'invoice_generated_at', 'invoice_generated_by', 'invoice_sent_at',
        'invoice_sent_by', 'billing_done_at', 'billing_done_by',
        // Closure's Collection Verification (Section 10 of the redesigned Closure screen)
        'collection_status', 'collection_amount', 'collection_date', 'payment_reference',
        'payment_method', 'outstanding_amount', 'collection_recorded_by', 'collection_recorded_at',
    ];

    protected $casts = [
        'invoice_generated_at' => 'datetime', 'invoice_sent_at' => 'datetime', 'billing_done_at' => 'datetime',
        'collection_amount' => 'decimal:2', 'collection_date' => 'date', 'outstanding_amount' => 'decimal:2',
        'collection_recorded_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function collectionRecordedBy()
    {
        return $this->belongsTo(User::class, 'collection_recorded_by');
    }
}
