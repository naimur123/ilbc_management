<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Request extends Model
{
    use SoftDeletes;

    protected $table = 'requests';

    protected $fillable = [
        'request_no', 'customer_id', 'salesperson_id', 'department_id',
        'work_order_no', 'po_number', 'order_date', 'status', 'current_stage',
        'created_by', 'submitted_at', 'current_stage_started_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'submitted_at' => 'datetime',
        'current_stage_started_at' => 'datetime',
    ];

    // Every stage a request can be in, in display order — used to render
    // the workflow timeline (Section 29) without hard-coding names anywhere else.
    public const STAGES = ['SALES', 'BILLING_CLEARANCE', 'REVIEWER', 'LOADING', 'AUDIT', 'BILLING', 'CLOSURE'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesperson()
    {
        return $this->belongsTo(Salesperson::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salesEntry()
    {
        return $this->hasOne(SalesEntry::class);
    }

    public function items()
    {
        return $this->hasMany(RequestItem::class);
    }

    public function attachments()
    {
        return $this->hasMany(RequestAttachment::class);
    }

    public function billingClearances()
    {
        return $this->hasMany(BillingClearance::class);
    }

    public function latestBillingClearance()
    {
        return $this->hasOne(BillingClearance::class)->latestOfMany('decided_at');
    }

    public function reviewerApproval()
    {
        return $this->hasOne(ReviewerApproval::class);
    }

    public function billingRecord()
    {
        return $this->hasOne(BillingRecord::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function closure()
    {
        return $this->hasOne(Closure::class);
    }

    public function closureChecklists()
    {
        return $this->hasMany(ClosureChecklist::class);
    }

    public function reopenRequests()
    {
        return $this->hasMany(ReopenRequest::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function totalSellingPrice(): float
    {
        return (float) $this->items()->sum('total_selling_price');
    }

    /**
     * REQ-YYMM-#### — matches the mockups (e.g. REQ-2609-0291).
     */
    public static function generateRequestNo(): string
    {
        $prefix = 'REQ-'.now()->format('ym').'-';
        $last = static::withTrashed()->where('request_no', 'like', $prefix.'%')->orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->request_no, -4)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
