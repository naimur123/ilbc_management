<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaRule extends Model
{
    protected $fillable = ['stage_key', 'label', 'sla_hours', 'due_soon_threshold_hours'];
}
