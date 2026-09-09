<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditChecklist extends Model
{
    protected $table = 'audit_checklists';
    protected $fillable = ['audit_record_id', 'check_key', 'label', 'is_checked'];
    protected $casts = ['is_checked' => 'boolean'];

    public function auditRecord()
    {
        return $this->belongsTo(AuditRecord::class);
    }
}
