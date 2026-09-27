<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplates extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'subject',
        'body',
        'cc',
        'bcc',
        'reply_to',
        'from_name',
        'from_email',
        'is_active',
    ];

    protected $casts = [
        'cc' => 'array',
        'bcc' => 'array',
        'reply_to' => 'array',
        'is_active' => 'boolean',
    ];

    public function email_attachments()
    {
        return $this->hasMany(EmailTemplateAttachment::class, 'email_template_id');
    }
}
