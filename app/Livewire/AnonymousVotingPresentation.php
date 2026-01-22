<?php

namespace App\Livewire;

use App\Models\VotingSession;
use Livewire\Component;

class AnonymousVotingPresentation extends Component
{
    public VotingSession $votingSession;

    public $candidates = [];
    public int $totalVotes = 0;
    public float $totalNpp = 0;
    public $leaderboard = [];
    public int $previousVoteCount = 0;
    public bool $hasNewVote = false;

    public function mount(VotingSession $votingSession)
    {
        $this->votingSession = $votingSession;
        $this->refreshStats();
    }

    public function refreshStats()
    {
        $this->votingSession->refresh();
        $this->votingSession->load(['candidates.ballots', 'ballots']);

        // Calculate totals
        $this->totalVotes = $this->votingSession->ballots->count();
        $this->totalNpp = $this->votingSession->ballots->sum('npp');

        // Get candidates with their vote counts
        $this->candidates = $this->votingSession->candidates->map(function ($candidate) {
            return [
                'id' => $candidate->id,
                'name' => $candidate->name,
                'sequence_number' => $candidate->sequence_number,
                'photo' => $candidate->photo,
                'total_votes' => $candidate->ballots->count(),
                'total_npp' => $candidate->ballots->sum('npp'),
            ];
        })->sortBy('sequence_number')->values();

        // Create leaderboard sorted by NPP
        $this->leaderboard = collect($this->candidates)->sortByDesc('total_npp')->values();

        // Check if there's a new vote
        if ($this->totalVotes > $this->previousVoteCount) {
            $this->hasNewVote = true;
            $this->dispatch('new-vote-added');
        } else {
            $this->hasNewVote = false;
        }
        $this->previousVoteCount = $this->totalVotes;
    }

    public function render()
    {
        return view('livewire.anonymous-voting-presentation');
    }
}
