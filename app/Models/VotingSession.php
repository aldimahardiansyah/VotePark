<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VotingSession extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'description',
        'status',
        'current_question_id',
        'allow_question_changes',
        'show_results_live',
        'voting_method',
        'started_at',
        'ended_at',
        'created_by',
    ];

    protected $casts = [
        'allow_question_changes' => 'boolean',
        'show_results_live' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function currentQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'current_question_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(VotingResponse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function canVote(): bool
    {
        return $this->isActive() && $this->current_question_id !== null;
    }

    public function getTotalParticipants(): int
    {
        return $this->event->site->tenants()->where('active', true)->count();
    }

    public function getResponsesForCurrentQuestion()
    {
        if (!$this->current_question_id) {
            return collect();
        }

        return $this->responses()
            ->where('question_id', $this->current_question_id)
            ->with(['answer', 'user'])
            ->get();
    }
}
