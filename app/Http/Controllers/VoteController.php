<?php

namespace App\Http\Controllers;

use App\Imports\VoteImport;
use App\Models\Question;
use App\Models\Vote;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VoteController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'file' => 'required|max:2048',
        ]);

        Excel::import(new VoteImport($request->event_id), $request->file('file'));

        return redirect()->back()->with('success', 'File imported successfully.');
    }

    public function show(Vote $vote)
    {
        $questions = Question::where('vote_id', $vote->id)->with(['answers', 'answers.units'])->get();
        $participants = $vote->event ? $vote->event->units()->with('user')->get() : collect();
        $vote->load('event');
        $userParticipants = $participants->pluck('user')->unique();
        $userParticipants->each(function ($participant) {
            $participant->load('units');
        });

        // Show vote details
        return view('contents.vote.show', [
            'vote' => $vote,
            'questions' => $questions,
            'participants' => $participants,
            'userParticipants' => $userParticipants,
        ]);
    }

    public function destroy(Vote $vote)
    {
        $vote->delete();

        return redirect()->back()->with('success', 'Vote deleted successfully.');
    }
}
