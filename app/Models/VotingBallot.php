<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VotingBallot extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'npp' => 'decimal:4',
    ];

    public function votingSession()
    {
        return $this->belongsTo(VotingSession::class);
    }

    public function votingCandidate()
    {
        return $this->belongsTo(VotingCandidate::class);
    }
}
