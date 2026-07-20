<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class JobRequiredSkill extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}
