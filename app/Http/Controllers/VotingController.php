<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\VotingSession;
use Illuminate\Http\Request;

class VotingController extends Controller
{
    public function manage(Event $event)
    {
        // Check if user can manage voting for this event
        $user = auth()->user();
        
        if (!$user->isSuperAdmin() && $user->site_id !== $event->site_id) {
            abort(403, 'You can only manage voting for events in your site.');
        }

        return view('voting.manage', compact('event'));
    }

    public function vote(Event $event)
    {
        // Tenant voting interface
        $user = auth()->user();
        
        if (!$user->isTenant()) {
            abort(403, 'Only tenants can access the voting interface.');
        }

        if ($user->site_id !== $event->site_id) {
            abort(403, 'You can only vote on events in your site.');
        }

        $votingSession = $event->getActiveVotingSession();
        
        if (!$votingSession) {
            return view('voting.no-session', compact('event'));
        }

        return view('voting.vote', compact('event', 'votingSession'));
    }

    public function display(Event $event)
    {
        // Public display for presentation
        $votingSession = $event->getActiveVotingSession();
        
        return view('voting.display', compact('event', 'votingSession'));
    }
}
