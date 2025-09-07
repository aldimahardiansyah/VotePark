<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

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

    public function votingSessions()
    {
        return $this->hasMany(VotingSession::class);
    }

    public function getActiveVotingSession()
    {
        return $this->votingSessions()
            ->where('status', 'active')
            ->first();
    }
}
