<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Closure extends Model
{
    protected $table = 'closures';
    protected $fillable = ['request_id', 'closed_by', 'closure_date', 'closure_time', 'remarks'];
    protected $casts = ['closure_date' => 'date'];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
