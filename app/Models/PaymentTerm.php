<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTerm extends Model
{
    protected $fillable = ['name', 'advance_percent', 'credit_days', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
