<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSkill extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'proficiency_level' => 'integer',
            'confidence_score' => 'decimal:2',
            'last_used_date' => 'date',
        ];
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}
