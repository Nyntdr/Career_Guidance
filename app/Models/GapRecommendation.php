<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GapRecommendation extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'rank' => 'integer',
        ];
    }

    public function gapAssessmentItem()
    {
        return $this->belongsTo(GapAssessmentItem::class);
    }

    public function learningResource()
    {
        return $this->belongsTo(LearningResource::class);
    }
}
