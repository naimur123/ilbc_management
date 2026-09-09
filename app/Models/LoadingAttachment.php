<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoadingAttachment extends Model
{
    protected $fillable = ['loading_record_id', 'type', 'original_name', 'path', 'mime_type', 'uploaded_by'];

    public function loadingRecord()
    {
        return $this->belongsTo(LoadingRecord::class);
    }
}
