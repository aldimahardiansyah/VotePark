<?php

namespace App\View\Components\Event;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Vote extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public $event,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.event.vote');
    }
}
