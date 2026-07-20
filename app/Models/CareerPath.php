<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CareerPath extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function fromRole()
    {
        return $this->belongsTo(CareerRole::class, 'from_role_id');
    }

    public function toRole()
    {
        return $this->belongsTo(CareerRole::class, 'to_role_id');
    }
}
