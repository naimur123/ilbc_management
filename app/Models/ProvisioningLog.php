<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvisioningLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['provisioning_job_id', 'request_payload', 'response_payload', 'http_status', 'created_at'];
    protected $casts = ['request_payload' => 'array', 'response_payload' => 'array', 'created_at' => 'datetime'];

    public function job()
    {
        return $this->belongsTo(ProvisioningJob::class, 'provisioning_job_id');
    }
}
