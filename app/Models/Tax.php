<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $fillable = ['name', 'type', 'rate_percent', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'rate_percent' => 'decimal:3'];
}
