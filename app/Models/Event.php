<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'requires_approval' => 'boolean',
        'requires_photo' => 'boolean',
        'date' => 'date',
    ];

    protected static $pivotColumns = [
        'unit_code',
        'status',
        'registered_email',
        'phone_number',
        'attendee_name',
        'attendance_type',
        'ownership_proof',
        'ppjb_document',
        'bukti_lunas_document',
        'sjb_shm_document',
        'civil_documents',
        'power_of_attorney',
        'identity_documents',
        'family_card',
        'company_documents',
        'participant_photo'
    ];

    public function units()
    {
        return $this->belongsToMany(Unit::class, 'event_unit')
            ->withPivot(static::$pivotColumns)
            ->withTimestamps();
    }

    public function approvedUnits()
    {
        return $this->belongsToMany(Unit::class, 'event_unit')
            ->withPivot(static::$pivotColumns)
            ->wherePivot('status', 'approved')
            ->withTimestamps();
    }

    public function pendingUnits()
    {
        return $this->belongsToMany(Unit::class, 'event_unit')
            ->withPivot(static::$pivotColumns)
            ->wherePivot('status', 'pending')
            ->withTimestamps();
    }

    public function rejectedUnits()
    {
        return $this->belongsToMany(Unit::class, 'event_unit')
            ->withPivot(static::$pivotColumns)
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
