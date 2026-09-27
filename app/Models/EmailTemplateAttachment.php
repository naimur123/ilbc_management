<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplateAttachment extends Model
{
    protected $fillable = ['email_template_id','path','name','mime_type','size'];

    public function email_template()
    {
        return $this->belongsTo(EmailTemplates::class, 'email_template_id');
    }
}
