<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['code', 'name', 'symbol', 'exchange_rate_to_base', 'is_base', 'is_active'];
    protected $casts = ['is_base' => 'boolean', 'is_active' => 'boolean', 'exchange_rate_to_base' => 'decimal:4'];
}
