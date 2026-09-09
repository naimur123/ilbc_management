<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowDefinition extends Model
{
    protected $fillable = ['name', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function steps()
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('step_order');
    }
}
