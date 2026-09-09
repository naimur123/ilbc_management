<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    protected $fillable = ['request_id', 'approval_rule_id', 'status', 'approved_by', 'approved_at', 'remarks'];
    protected $casts = ['approved_at' => 'datetime'];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function rule()
    {
        return $this->belongsTo(ApprovalRule::class, 'approval_rule_id');
    }
}
