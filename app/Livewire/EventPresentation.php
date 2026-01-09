<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Component;

class EventPresentation extends Component
{
    public Event $event;

    public int $registeredUnits = 0;
    public float $totalNpp = 0;
    public int $uniqueOwners = 0;
    public $approvedParticipants = [];
    public int $previousCount = 0;
    public bool $hasNewData = false;

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->refreshStats();
    }

    public function refreshStats()
    {
        $this->event->refresh();
        $approvedUnits = $this->event->approvedUnits();
        $this->registeredUnits = $approvedUnits->count();
        $this->totalNpp = $approvedUnits->sum('npp');
        $this->uniqueOwners = $approvedUnits->pluck('user_id')->unique()->count();

        // Get approved participants ordered by latest first
        $newParticipants = $approvedUnits
            ->with('user')
            ->orderByPivot('created_at', 'desc')
            ->get();

        // Check if there's new data
        $newCount = $newParticipants->count();
        if ($newCount > $this->previousCount) {
            $this->hasNewData = true;
            $this->dispatch('new-participant-added');
        } else {
            $this->hasNewData = false;
        }
        $this->previousCount = $newCount;

        $this->approvedParticipants = $newParticipants;
    }

    public function render()
    {
        return view('livewire.event-presentation2');
    }
}
