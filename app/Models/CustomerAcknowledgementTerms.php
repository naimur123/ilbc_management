<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAcknowledgementTerms extends Model
{
    protected $fillable = [
        'term_key',
        'title',
        'description',
        'sort_order',
        'version',
        'is_active',
    ];
    protected $casts = [
        'is_active' => 'boolean',
        'version' => 'integer',
        'sort_order' => 'integer',
    ];
}
