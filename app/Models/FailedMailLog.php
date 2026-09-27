<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedMailLog extends Model
{
    protected $fillable = [
        'template_slug',
        'to_email',
        'payload',
        'attachments',
        'error_message',
        'error_trace',
        'failed_at',
        'status',
        'attempt_count',
        'transport',
        'last_attempt_at',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'attachments' => 'array',
        'failed_at' => 'datetime',
        'attempt_count' => 'integer',
        'last_attempt_at' => 'datetime',
        'sent_at' => 'datetime',
    ];
}
