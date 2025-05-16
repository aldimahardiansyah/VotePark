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
