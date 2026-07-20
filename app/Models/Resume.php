<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'parsed_json' => 'json',
            'version' => 'integer',
            'is_primary' => 'boolean',
            'uploaded_at' => 'datetime',
        ];
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function sections()
    {
        return $this->hasMany(ResumeSection::class);
    }

    public function atsScans()
    {
        return $this->hasMany(AtsScan::class);
    }
}
