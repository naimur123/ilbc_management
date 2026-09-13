<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaStatusHistory extends Model
{
    protected $table = 'sla_status_history';

    protected $fillable = ['request_sla_id', 'from_status', 'to_status', 'changed_by', 'remarks'];

    public function requestSla()
    {
        return $this->belongsTo(RequestSla::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
