<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoadingChecklist extends Model
{
    protected $fillable = ['loading_record_id', 'check_key', 'label', 'is_checked'];
    protected $casts = ['is_checked' => 'boolean'];

    public function loadingRecord()
    {
        return $this->belongsTo(LoadingRecord::class);
    }
}
