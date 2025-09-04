<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_npp_based' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function vote(): BelongsTo
    {
        return $this->belongsTo(Vote::class);
    }

    public function votingSession(): BelongsTo
    {
        return $this->belongsTo(VotingSession::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(VotingResponse::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getVotingResults()
    {
        $responses = $this->responses()->with(['answer', 'user'])->get();
        
        $results = [];
        foreach ($this->answers as $answer) {
            $answerResponses = $responses->where('answer_id', $answer->id);
            
            if ($this->is_npp_based) {
                $totalNppValue = $answerResponses->sum('npp_value');
                $count = $answerResponses->count();
                $percentage = $responses->sum('npp_value') > 0 
                    ? round(($totalNppValue / $responses->sum('npp_value')) * 100, 2)
                    : 0;
            } else {
                $count = $answerResponses->count();
                $totalNppValue = $count; // For display consistency
                $percentage = $responses->count() > 0 
                    ? round(($count / $responses->count()) * 100, 2)
                    : 0;
            }
            
            $results[] = [
                'answer' => $answer,
                'count' => $count,
                'npp_value' => $totalNppValue,
                'percentage' => $percentage,
            ];
        }
        
        return $results;
    }
}
