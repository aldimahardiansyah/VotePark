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

    public function mount(Event $event)
    {
        $this->event = $event;
        $this->refreshStats();
    }

    public function refreshStats()
    {
        $this->event->refresh();
        $approvedUnits = $this->event->approvedUnits;
        $this->registeredUnits = $approvedUnits->count();
        $this->totalNpp = $approvedUnits->sum('npp');
        $this->uniqueOwners = $approvedUnits->pluck('user_id')->unique()->count();
    }

    public function render()
    {
        return view('livewire.event-presentation');
    }
}
