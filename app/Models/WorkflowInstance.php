<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowInstance extends Model
{
    protected $fillable = ['request_id', 'workflow_definition_id', 'current_step_id'];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function currentStep()
    {
        return $this->belongsTo(WorkflowStep::class, 'current_step_id');
    }

    public function actions()
    {
        return $this->hasMany(WorkflowAction::class)->orderBy('performed_at');
    }
}
