<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProgressTracking extends Model
{
    use HasUuids;

    protected $table = 'progress_tracking';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function gapAssessmentItem()
    {
        return $this->belongsTo(GapAssessmentItem::class);
    }
}
