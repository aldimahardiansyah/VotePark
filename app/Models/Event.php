<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'requires_approval' => 'boolean',
        'date' => 'date',
    ];

    public function units()
    {
        return $this->belongsToMany(Unit::class, 'event_unit')
            ->withPivot('unit_code', 'status', 'registered_email')
            ->withTimestamps();
    }

    public function approvedUnits()
    {
        return $this->belongsToMany(Unit::class, 'event_unit')
            ->withPivot('unit_code', 'status', 'registered_email')
            ->wherePivot('status', 'approved')
            ->withTimestamps();
    }

    public function pendingUnits()
    {
        return $this->belongsToMany(Unit::class, 'event_unit')
            ->withPivot('unit_code', 'status', 'registered_email')
            ->wherePivot('status', 'pending')
            ->withTimestamps();
    }

    public function rejectedUnits()
    {
        return $this->belongsToMany(Unit::class, 'event_unit')
            ->withPivot('unit_code', 'status', 'registered_email')
            ->wherePivot('status', 'rejected')
            ->withTimestamps();
    }

    public function getUnitCodeAttribute()
    {
        return $this->units->pluck('pivot.unit_code')->toArray();
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
