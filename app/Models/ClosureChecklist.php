<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClosureChecklist extends Model
{
    protected $fillable = ['request_id', 'check_key', 'label', 'is_checked', 'is_mandatory'];

    protected $casts = ['is_checked' => 'boolean', 'is_mandatory' => 'boolean'];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }
}
