<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowStep extends Model
{
    protected $fillable = ['workflow_definition_id', 'step_key', 'name', 'step_order', 'required_permission', 'sla_hours'];

    public function definition()
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }
}
