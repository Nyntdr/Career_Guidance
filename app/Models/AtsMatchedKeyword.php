<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AtsMatchedKeyword extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function atsScan()
    {
        return $this->belongsTo(AtsScan::class);
    }
}
