<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRule extends Model
{
    protected $fillable = ['name', 'condition_field', 'operator', 'threshold_value', 'required_role', 'is_active'];
    protected $casts = ['threshold_value' => 'decimal:4', 'is_active' => 'boolean'];
}
