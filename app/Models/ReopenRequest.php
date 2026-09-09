<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReopenRequest extends Model
{
    protected $fillable = [
        'request_id', 'reason', 'target_stage', 'requested_by', 'requested_at',
        'status', 'approved_by', 'approved_at', 'decision_remarks',
    ];

    protected $casts = ['requested_at' => 'datetime', 'approved_at' => 'datetime'];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
