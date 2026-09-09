<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditRecord extends Model
{
    protected $table = 'audit_records';

    protected $fillable = [
        'request_item_id', 'loading_record_id', 'decision', 'remarks',
        'has_variance', 'audited_by', 'audited_at',
    ];

    protected $casts = ['has_variance' => 'boolean', 'audited_at' => 'datetime'];

    public function requestItem()
    {
        return $this->belongsTo(RequestItem::class);
    }

    public function loadingRecord()
    {
        return $this->belongsTo(LoadingRecord::class);
    }

    public function checklists()
    {
        return $this->hasMany(AuditChecklist::class);
    }

    public function variances()
    {
        return $this->hasMany(AuditVariance::class);
    }

    public function corrections()
    {
        return $this->hasMany(AuditCorrection::class);
    }

    public function auditedBy()
    {
        return $this->belongsTo(User::class, 'audited_by');
    }
}
