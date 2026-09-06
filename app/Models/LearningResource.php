<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LearningResource extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'duration_hours' => 'decimal:2',
            'cost' => 'decimal:2',
        ];
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'learning_resource_skills')
            ->using(LearningResourceSkill::class);
    }

    public function gapRecommendations()
    {
        return $this->hasMany(GapRecommendation::class);
    }
}
