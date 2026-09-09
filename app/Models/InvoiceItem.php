<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = ['invoice_id', 'request_item_id', 'description', 'quantity', 'unit_price', 'line_total'];
    protected $casts = ['quantity' => 'decimal:2', 'unit_price' => 'decimal:2', 'line_total' => 'decimal:2'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function requestItem()
    {
        return $this->belongsTo(RequestItem::class);
    }
}
