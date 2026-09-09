<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salesperson extends Model
{
    use SoftDeletes;

    protected $table = 'salespersons';
    protected $fillable = ['user_id', 'name', 'code', 'department_id', 'phone', 'email', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }
}
