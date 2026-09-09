<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditVariance extends Model
{
    protected $table = 'audit_variances';
    protected $fillable = ['audit_record_id', 'field_key', 'label', 'approved_value', 'actual_value', 'is_mismatch'];
    protected $casts = ['is_mismatch' => 'boolean'];

    public function auditRecord()
    {
        return $this->belongsTo(AuditRecord::class);
    }
}
