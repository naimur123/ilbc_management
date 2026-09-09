<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewerChecklist extends Model
{
    protected $fillable = ['reviewer_approval_id', 'check_key', 'label', 'is_checked', 'is_mandatory'];
    protected $casts = ['is_checked' => 'boolean', 'is_mandatory' => 'boolean'];

    public function reviewerApproval()
    {
        return $this->belongsTo(ReviewerApproval::class);
    }
}
