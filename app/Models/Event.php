<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = ['id'];

    public function units()
    {
        return $this->belongsToMany(Unit::class, 'event_unit')->withPivot('unit_code');
    }

    public function getUnitCodeAttribute()
    {
        return $this->units->pluck('pivot.unit_code')->toArray();
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
