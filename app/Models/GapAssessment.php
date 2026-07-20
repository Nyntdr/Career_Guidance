<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GapAssessment extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function targetRole()
    {
        return $this->belongsTo(CareerRole::class, 'target_role_id');
    }

    public function targetJobPosting()
    {
        return $this->belongsTo(JobPosting::class, 'target_job_posting_id');
    }

    public function items()
    {
        return $this->hasMany(GapAssessmentItem::class);
    }
}
