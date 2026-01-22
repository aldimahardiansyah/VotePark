<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VotingCandidate extends Model
{
    protected $guarded = ['id'];

    public function votingSession()
    {
        return $this->belongsTo(VotingSession::class);
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
