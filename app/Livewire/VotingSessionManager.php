<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\VotingSession;
use App\Models\Question;
use App\Models\Answer;
use Livewire\Component;
use Livewire\Attributes\On;

class VotingSessionManager extends Component
{
    public Event $event;
    public $votingSession = null;
    public $currentQuestionId = null;
    public $questions = [];
    public $newQuestionText = '';
    public $newAnswers = ['', ''];
    public $sessionName = '';
    public $sessionDescription = '';
    public $votingMethod = 'one_man_one_vote';

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->loadVotingSession();
        $this->sessionName = $this->event->name . ' - Voting Session';
    }

    public function loadVotingSession()
    {
        $this->votingSession = $this->event->getActiveVotingSession() 
            ?? $this->event->votingSessions()->where('status', 'draft')->first();
        
        if ($this->votingSession) {
            $this->currentQuestionId = $this->votingSession->current_question_id;
            $this->loadQuestions();
        }
    }

    public function loadQuestions()
    {
        if ($this->votingSession) {
            $this->questions = $this->votingSession->questions()
                ->with('answers')
                ->orderBy('order')
                ->get()
                ->toArray();
        }
    }

    public function createSession()
    {
        $this->validate([
            'sessionName' => 'required|string|max:255',
            'votingMethod' => 'required|in:one_man_one_vote,npp_based',
        ]);

        $this->votingSession = VotingSession::create([
            'event_id' => $this->event->id,
            'name' => $this->sessionName,
            'description' => $this->sessionDescription,
            'voting_method' => $this->votingMethod,
            'created_by' => auth()->id(),
        ]);

        $this->loadQuestions();
        $this->sessionName = '';
        $this->sessionDescription = '';
        
        session()->flash('message', 'Voting session created successfully!');
    }

    public function addQuestion()
    {
        $this->validate([
            'newQuestionText' => 'required|string|max:1000',
            'newAnswers.*' => 'required|string|max:255',
        ]);

        if (!$this->votingSession) {
            session()->flash('error', 'Please create a voting session first.');
            return;
        }

        $question = Question::create([
            'voting_session_id' => $this->votingSession->id,
            'question' => $this->newQuestionText,
            'is_npp_based' => $this->votingMethod === 'npp_based',
            'order' => count($this->questions) + 1,
        ]);

        foreach ($this->newAnswers as $answerText) {
            if (!empty(trim($answerText))) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer' => trim($answerText),
                ]);
            }
        }

        $this->newQuestionText = '';
        $this->newAnswers = ['', ''];
        $this->loadQuestions();
        
        session()->flash('message', 'Question added successfully!');
    }

    public function addAnswerField()
    {
        $this->newAnswers[] = '';
    }

    public function removeAnswerField($index)
    {
        if (count($this->newAnswers) > 2) {
            unset($this->newAnswers[$index]);
            $this->newAnswers = array_values($this->newAnswers);
        }
    }

    public function startSession()
    {
        if (!$this->votingSession || empty($this->questions)) {
            session()->flash('error', 'Please add at least one question before starting the session.');
            return;
        }

        $this->votingSession->update([
            'status' => 'active',
            'started_at' => now(),
        ]);

        $this->loadVotingSession();
        
        // Dispatch event to update other components
        $this->dispatch('voting-session-started', $this->votingSession->id);
        
        session()->flash('message', 'Voting session started!');
    }

    public function pauseSession()
    {
        try {
            if (!$this->votingSession) {
                session()->flash('error', 'No voting session found.');
                return;
            }

            $this->votingSession->update([
                'status' => 'paused',
            ]);

            $this->loadVotingSession();
            $this->dispatch('voting-session-paused', $this->votingSession->id);
            
            session()->flash('message', 'Voting session paused.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error pausing session: ' . $e->getMessage());
        }
    }

    public function resumeSession()
    {
        $this->votingSession->update([
            'status' => 'active',
        ]);

        $this->loadVotingSession();
        $this->dispatch('voting-session-resumed', $this->votingSession->id);
        
        session()->flash('message', 'Voting session resumed!');
    }

    public function endSession()
    {
        try {
            if (!$this->votingSession) {
                session()->flash('error', 'No voting session found.');
                return;
            }

            // End current question if active
            if ($this->currentQuestionId) {
                $this->endCurrentQuestion();
            }

            $this->votingSession->update([
                'status' => 'completed',
                'ended_at' => now(),
                'current_question_id' => null,
            ]);

            $this->loadVotingSession();
            $this->dispatch('voting-session-ended', $this->votingSession->id);
            
            session()->flash('message', 'Voting session completed!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error ending session: ' . $e->getMessage());
        }
    }

    public function activateQuestion($questionId)
    {
        if (!$this->votingSession || !$this->votingSession->isActive()) {
            session()->flash('error', 'Voting session must be active to start a question.');
            return;
        }

        // End current question if any
        if ($this->currentQuestionId) {
            $this->endCurrentQuestion();
        }

        // Start new question
        Question::where('id', $questionId)->update([
            'status' => 'active',
            'started_at' => now(),
        ]);

        $this->votingSession->update([
            'current_question_id' => $questionId,
        ]);

        $this->currentQuestionId = $questionId;
        
        // Dispatch event to update voting interfaces
        $this->dispatch('question-activated', $questionId);
        
        session()->flash('message', 'Question activated for voting!');
    }

    public function endCurrentQuestion()
    {
        try {
            if ($this->currentQuestionId) {
                Question::where('id', $this->currentQuestionId)->update([
                    'status' => 'completed',
                    'ended_at' => now(),
                ]);

                $this->dispatch('question-ended', $this->currentQuestionId);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error ending current question: ' . $e->getMessage());
        }
    }

    public function nextQuestion()
    {
        if (!$this->currentQuestionId) {
            return;
        }

        $currentIndex = collect($this->questions)->search(function ($question) {
            return $question['id'] == $this->currentQuestionId;
        });

        if ($currentIndex !== false && $currentIndex < count($this->questions) - 1) {
            $nextQuestion = $this->questions[$currentIndex + 1];
            $this->activateQuestion($nextQuestion['id']);
        } else {
            session()->flash('message', 'This is the last question.');
        }
    }

    #[On('voting-response-submitted')]
    public function onVotingResponse()
    {
        // Refresh the component to show updated results
        $this->loadQuestions();
    }

    public function render()
    {
        return view('livewire.voting-session-manager');
    }
}
