<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'target_roles' => 'array',
            'target_industries' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function education()
    {
        return $this->hasMany(Education::class);
    }

    public function workExperience()
    {
        return $this->hasMany(WorkExperience::class);
    }

    public function userSkills()
    {
        return $this->hasMany(UserSkill::class);
    }

    public function resumes()
    {
        return $this->hasMany(Resume::class);
    }

    public function careerRecommendations()
    {
        return $this->hasMany(CareerRecommendation::class);
    }

    public function progressTracking()
    {
        return $this->hasMany(ProgressTracking::class);
    }
}
