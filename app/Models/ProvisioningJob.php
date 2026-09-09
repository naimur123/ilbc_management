<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvisioningJob extends Model
{
    protected $fillable = [
        'request_item_id', 'loading_record_id', 'provider', 'action', 'status',
        'external_reference', 'attempts', 'last_error', 'triggered_by',
    ];

    public function requestItem()
    {
        return $this->belongsTo(RequestItem::class);
    }

    public function loadingRecord()
    {
        return $this->belongsTo(LoadingRecord::class);
    }

    public function logs()
    {
        return $this->hasMany(ProvisioningLog::class);
    }
}
