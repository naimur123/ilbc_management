<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_category_id', 'name', 'product_line', 'segment',
        'licensing_program', 'description', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }
}
