<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
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
}
