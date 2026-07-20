<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareerRole extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'avg_salary_range' => 'json',
        ];
    }

    public function skills()
    {
        return $this->hasMany(CareerRoleSkill::class);
    }

    public function requiredSkills()
    {
        return $this->belongsToMany(Skill::class, 'career_role_skills')
            ->withPivot(['importance', 'min_proficiency']);
    }

    public function outgoingPaths()
    {
        return $this->hasMany(CareerPath::class, 'from_role_id');
    }

    public function incomingPaths()
    {
        return $this->hasMany(CareerPath::class, 'to_role_id');
    }

    public function recommendations()
    {
        return $this->hasMany(CareerRecommendation::class);
    }
}
