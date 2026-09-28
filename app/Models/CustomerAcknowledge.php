<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAcknowledge extends Model
{
    protected $fillable = [
        'customer_id',
        'request_item_id',
        'loading_record_id',
        'terms_accepted',
        'terms_version',
        'terms_snapshot',
        'declaration_snapshot',
        'accepted_company_name',
        'terms_accepted_at',
        'accepted_ip_address',
        'accepted_user_agent',
    ];

    protected $casts = [
        'terms_accepted' => 'boolean',
        'terms_version' => 'integer',
        'terms_snapshot' => 'array',
        'terms_accepted_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(
            Customer::class
        );
    }

    public function requestItem()
    {
        return $this->belongsTo(
            RequestItem::class
        );
    }

    public function loadingRecord()
    {
        return $this->belongsTo(
            LoadingRecord::class
        );
    }
}