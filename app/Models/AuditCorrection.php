<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditCorrection extends Model
{
    protected $table = 'audit_corrections';
    protected $fillable = ['audit_record_id', 'category', 'remarks'];

    public function auditRecord()
    {
        return $this->belongsTo(AuditRecord::class);
    }
}
