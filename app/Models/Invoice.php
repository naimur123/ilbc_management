<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'request_id', 'invoice_no', 'invoice_date', 'billing_month', 'billing_period_from',
        'billing_period_to', 'invoice_amount', 'vat_amount', 'tax_amount', 'other_charges',
        'total_amount', 'due_date', 'file_path', 'remarks', 'status', 'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date', 'billing_period_from' => 'date', 'billing_period_to' => 'date',
        'due_date' => 'date', 'invoice_amount' => 'decimal:2', 'vat_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2', 'other_charges' => 'decimal:2', 'total_amount' => 'decimal:2',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
