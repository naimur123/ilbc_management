<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorContact extends Model
{
    protected $fillable = ['vendor_id', 'name', 'designation', 'phone', 'email', 'is_primary'];
    protected $casts = ['is_primary' => 'boolean'];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
