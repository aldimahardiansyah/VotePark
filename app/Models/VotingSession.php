<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VotingSession extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function candidates()
    {
        return $this->hasMany(VotingCandidate::class)->orderBy('sequence_number');
    }

    public function ballots()
    {
        return $this->hasMany(VotingBallot::class);
    }

    public function getTotalVotesAttribute()
    {
        return $this->ballots()->count();
    }

    public function getTotalNppAttribute()
    {
        return $this->ballots()->sum('npp');
    }
}
