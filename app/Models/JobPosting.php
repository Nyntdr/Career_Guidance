<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'parsed_requirements' => 'json',
            'embedding' => 'json',
            'posted_at' => 'datetime',
        ];
    }

    public function requiredSkills()
    {
        return $this->hasMany(JobRequiredSkill::class);
    }

    public function atsScans()
    {
        return $this->hasMany(AtsScan::class);
    }
}
