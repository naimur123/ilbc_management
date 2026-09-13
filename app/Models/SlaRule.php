<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaRule extends Model
{
    protected $fillable = ['stage_key', 'label', 'sla_hours', 'due_soon_threshold_hours'];

    protected $casts = [
        'sla_hours' => 'integer',
        'due_soon_threshold_hours' => 'integer',
    ];
}
