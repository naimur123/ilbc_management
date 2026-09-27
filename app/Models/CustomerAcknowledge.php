<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAcknowledge extends Model
{
     protected $fillable = [
        'customer_id',
        'request_item_id',
        'loading_record_id',
        'signatory_name',
        'designation',
        'acknowledgement_date',
        'signature',
    ];
}
