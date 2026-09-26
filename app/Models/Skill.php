<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(SkillCategory::class, 'category_id');
    }

    public function aliases()
    {
        return $this->hasMany(SkillAlias::class);
    }

    public function userSkills()
    {
        return $this->hasMany(UserSkill::class);
    }

    public function careerRoleSkills()
    {
        return $this->hasMany(CareerRoleSkill::class);
    }

    public function jobRequiredSkills()
    {
        return $this->hasMany(JobRequiredSkill::class);
    }

    public function learningResources()
    {
        return $this->belongsToMany(LearningResource::class, 'learning_resource_skills');
    }
}
