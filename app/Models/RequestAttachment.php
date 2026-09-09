<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestAttachment extends Model
{
    protected $fillable = ['request_id', 'type', 'original_name', 'path', 'mime_type', 'size_bytes', 'uploaded_by'];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }
}
