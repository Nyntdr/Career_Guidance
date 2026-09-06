<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class LearningResourceSkill extends Pivot
{
    use HasUuids;

    protected $guarded = [];

    public $timestamps = false;

    public function learningResource()
    {
        return $this->belongsTo(LearningResource::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}
