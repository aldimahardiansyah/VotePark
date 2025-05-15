<?php

namespace App\View\Components\Event;

use App\Models\Event;
use App\Models\Unit;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AddParticipantModal extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public $event,
    ) {
        //
    }

    public function users()
    {
        return User::whereHas('units')->with('units')->get();
    }

    public function units()
    {
        // Get all units attached to the event
        $units = $this->event->units()->pluck('unit_id')->toArray();

        return Unit::whereHas('user')->with('user')->whereNotIn('id', $units)->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.event.add-participant-modal');
    }
}
