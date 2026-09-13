<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestSla extends Model
{
    protected $fillable = [
        'request_id', 'request_item_id', 'sla_configuration_id', 'sla_type', 'priority',
        'start_at', 'target_at', 'duration_minutes', 'reminder_before_minutes', 'escalate_after_minutes',
        'responsible_user_id', 'responsible_team', 'escalated_to_user_id', 'status',
        'completed_at', 'completed_by', 'remarks', 'waived_reason', 'waived_by', 'waived_at',
        'reminder_sent_at', 'escalated_at', 'attachment_path', 'attachment_original_name', 'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime', 'target_at' => 'datetime', 'completed_at' => 'datetime',
        'waived_at' => 'datetime', 'reminder_sent_at' => 'datetime', 'escalated_at' => 'datetime',
        'duration_minutes' => 'integer', 'reminder_before_minutes' => 'integer',
        'escalate_after_minutes' => 'integer',
    ];

    public const FINAL_STATUSES = ['COMPLETED_WITHIN_SLA', 'COMPLETED_LATE', 'WAIVED'];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function requestItem()
    {
        return $this->belongsTo(RequestItem::class);
    }

    public function configuration()
    {
        return $this->belongsTo(SlaConfiguration::class, 'sla_configuration_id');
    }

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function escalatedToUser()
    {
        return $this->belongsTo(User::class, 'escalated_to_user_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function waivedBy()
    {
        return $this->belongsTo(User::class, 'waived_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusHistory()
    {
        return $this->hasMany(SlaStatusHistory::class)->latest();
    }

    public function isFinal(): bool
    {
        return in_array($this->status, self::FINAL_STATUSES, true);
    }
}
