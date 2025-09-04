<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Event;
use App\Models\VotingSession;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get events based on user role
        if ($user->isSuperAdmin()) {
            $events = Event::with('votingSessions.questions')->get();
        } else {
            $events = Event::where('site_id', $user->site_id)
                          ->with('votingSessions.questions')
                          ->get();
        }
        
        return view('contents.question.index', compact('events'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        
        // Get events for dropdown based on user role
        if ($user->isSuperAdmin()) {
            $events = Event::all();
        } else {
            $events = Event::where('site_id', $user->site_id)->get();
        }
        
        $selectedEventId = $request->get('event_id');
        $selectedEvent = $selectedEventId ? Event::find($selectedEventId) : null;
        
        return view('contents.question.create', compact('events', 'selectedEvent'));
    }

    public function show($id)
    {
        $question = Question::find($id);
        if (!$question) {
            return redirect()->back()->with('error', 'Question not found.');
        }

        $participants = $question->vote ? $question->vote->event->units()->with('user')->get() : collect();
        $question->load('answers.units');
        $userParticipants = $participants->pluck('user')->unique();
        $userParticipants->each(function ($participant) {
            $participant->load('units');
        });

        return view('contents.question.show', compact('question', 'participants', 'userParticipants'));
    }

    public function update(Request $request, $id)
    {
        $question = Question::find($id);
        if (!$question) {
            return redirect()->back()->with('error', 'Question not found.');
        }

        $request->validate([
            'is_npp_based' => 'required|boolean',
        ]);

        $question->update([
            'is_npp_based' => $request->input('is_npp_based'),
        ]);

        return redirect()->back()->with('success', 'Question updated successfully.');
    }
}
