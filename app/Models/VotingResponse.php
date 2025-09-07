<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VotingResponse extends Model
{
    protected $fillable = [
        'voting_session_id',
        'question_id',
        'answer_id',
        'user_id',
        'unit_code',
        'npp_value',
        'voted_at',
    ];

    protected $casts = [
        'npp_value' => 'decimal:2',
        'voted_at' => 'datetime',
    ];

    public function votingSession(): BelongsTo
    {
        return $this->belongsTo(VotingSession::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(Answer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
