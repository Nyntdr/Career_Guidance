<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GapAssessmentItem extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'required_level' => 'integer',
            'current_level' => 'integer',
            'gap_size' => 'integer',
        ];
    }

    public function gapAssessment()
    {
        return $this->belongsTo(GapAssessment::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function recommendations()
    {
        return $this->hasMany(GapRecommendation::class);
    }

    public function progressTracking()
    {
        return $this->hasMany(ProgressTracking::class);
    }
}
