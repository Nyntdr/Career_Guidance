<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SkillAlias extends Model
{
    use HasUuids;

    protected $guarded = [];

    public $timestamps = false;

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}
