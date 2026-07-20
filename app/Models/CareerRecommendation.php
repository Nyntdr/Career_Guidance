<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CareerRecommendation extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'match_score' => 'decimal:2',
            'generated_at' => 'datetime',
        ];
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function careerRole()
    {
        return $this->belongsTo(CareerRole::class);
    }
}
