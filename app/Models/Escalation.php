<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Escalation extends Model
{
    protected $fillable = ['request_id', 'stage_key', 'escalated_at', 'escalated_to', 'notes'];
    protected $casts = ['escalated_at' => 'datetime'];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }
}
