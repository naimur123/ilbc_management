<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowAction extends Model
{
    protected $fillable = ['workflow_instance_id', 'workflow_step_id', 'action', 'performed_by', 'remarks', 'performed_at'];
    protected $casts = ['performed_at' => 'datetime'];

    public function instance()
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function step()
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
