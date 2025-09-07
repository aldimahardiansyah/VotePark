<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\VotingSession;
use App\Models\VotingResponse;
use App\Models\Question;
use Livewire\Component;
use Livewire\Attributes\On;

class VotingInterface extends Component
{
    public Event $event;
    public $votingSession = null;
    public $currentQuestion = null;
    public $selectedAnswerId = null;
    public $hasVoted = false;
    public $userResponse = null;

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->loadVotingSession();
    }

    public function loadVotingSession()
    {
        $this->votingSession = $this->event->getActiveVotingSession();
        
        if ($this->votingSession && $this->votingSession->current_question_id) {
            $this->currentQuestion = Question::with('answers')->find($this->votingSession->current_question_id);
            $this->checkUserVote();
        }
    }

    public function checkUserVote()
    {
        if ($this->currentQuestion) {
            $this->userResponse = VotingResponse::where([
                'voting_session_id' => $this->votingSession->id,
                'question_id' => $this->currentQuestion->id,
                'user_id' => auth()->id(),
            ])->first();
            
            if ($this->userResponse) {
                $this->selectedAnswerId = $this->userResponse->answer_id;
                $this->hasVoted = true;
            } else {
                $this->selectedAnswerId = null;
                $this->hasVoted = false;
            }
        }
    }

    public function selectAnswer($answerId)
    {
        if (!$this->votingSession || !$this->currentQuestion || !$this->votingSession->canVote()) {
            return;
        }

        $this->selectedAnswerId = $answerId;
    }

    public function submitVote()
    {
        if (!$this->selectedAnswerId || !$this->currentQuestion || !$this->votingSession->canVote()) {
            session()->flash('error', 'Please select an answer before voting.');
            return;
        }

        $user = auth()->user();
        $nppValue = $this->currentQuestion->is_npp_based ? ($user->units()->first()->npp ?? 1) : 1;

        // Update or create vote
        VotingResponse::updateOrCreate(
            [
                'voting_session_id' => $this->votingSession->id,
                'question_id' => $this->currentQuestion->id,
                'user_id' => $user->id,
            ],
            [
                'answer_id' => $this->selectedAnswerId,
                'unit_code' => $user->unit_code,
                'npp_value' => $nppValue,
                'voted_at' => now(),
            ]
        );

        $this->hasVoted = true;
        $this->dispatch('voting-response-submitted');
        session()->flash('message', 'Your vote has been submitted successfully!');
    }

    #[On('question-activated')]
    public function onQuestionActivated($questionId)
    {
        $this->loadVotingSession();
    }

    #[On('question-ended')]
    public function onQuestionEnded($questionId)
    {
        $this->currentQuestion = null;
        $this->selectedAnswerId = null;
        $this->hasVoted = false;
    }

    #[On('voting-session-ended')]
    public function onVotingSessionEnded()
    {
        $this->votingSession = null;
        $this->currentQuestion = null;
        $this->selectedAnswerId = null;
        $this->hasVoted = false;
    }

    public function render()
    {
        return view('livewire.voting-interface');
    }
}
