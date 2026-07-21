<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SkillCategory extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function skills()
    {
        return $this->hasMany(Skill::class, 'category_id');
    }
}
