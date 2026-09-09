<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_code', 'name', 'contact_person', 'mobile', 'email', 'address',
        'department_id', 'source', 'customer_type', 'payment_terms_id',
        'credit_limit', 'outstanding_balance', 'status', 'remarks',
    ];

    protected $casts = ['credit_limit' => 'decimal:2', 'outstanding_balance' => 'decimal:2'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_terms_id');
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function isOverCreditLimit(): bool
    {
        return $this->outstanding_balance > $this->credit_limit;
    }
}
