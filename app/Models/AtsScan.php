<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtsScan extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function details()
    {
        return $this->hasMany(AtsScanDetail::class);
    }

    public function matchedKeywords()
    {
        return $this->hasMany(AtsMatchedKeyword::class);
    }
}
