<?php

namespace App\Imports;

use App\Models\Answer;
use App\Models\Event;
use App\Models\Question;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class VoteImport implements ToCollection
{
    public $eventId;

    public function __construct($eventId)
    {
        $this->eventId = $eventId;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        // validate first row
        if ($collection[0][2] != 'Participant Email' || $collection[0][5] != 'Poll Question' || $collection[0][6] != 'Poll Option') {
            return redirect()->back()->with('error', 'Invalid file format. Please check the file and try again.');
        }

        DB::beginTransaction();
        try {
            // Create vote
            $vote = Vote::create([
                'event_id' => $this->eventId,
                'name' => now(),
            ]);
            if (!$vote) {
                throw new \Exception('Failed to create vote.');
            }

            foreach ($collection as $row) {
                if ($row[2] == 'Participant Email' || $row[5] == 'Poll Question' || $row[6] == 'Poll Option') {
                    continue; // Skip header row
                }

                // Process each row
                $participantEmail = $row[2];
                $pollQuestion = $row[5];
                $pollOption = $row[6];

                // Check if participant exists
                $user = User::where('email', $participantEmail)->get();
                if ($user->isEmpty()) {
                    Log::error('Participant not found: ' . $participantEmail);
                    continue; // Skip to the next row
                }

                // Get units on event
                $unitsOnEvent = Event::find($this->eventId)->units()->whereHas('user', function ($query) use ($participantEmail) {
                    $query->where('email', $participantEmail);
                })->get();
                if ($unitsOnEvent->isEmpty()) {
                    Log::error('User is not on event: ' . $participantEmail);
                    continue; // Skip to the next row
                }

                // Create question if it doesn't exist
                $question = Question::firstOrCreate([
                    'vote_id' => $vote->id,
                    'desc' => $pollQuestion,
                ]);

                // Update or create answer
                $answer = Answer::updateOrCreate([
                    'question_id' => $question->id,
                    'desc' => $pollOption,
                ], [
                    'votes' => DB::raw('votes + 1'),
                    'npp_votes' => DB::raw("npp_votes + {$unitsOnEvent->sum('npp')}"),
                ]);

                // Create answerUnit pivot table
                foreach ($unitsOnEvent as $unit) {
                    $answer->units()->attach($unit->id, [
                        'vote_id' => $vote->id,
                        'question_id' => $question->id,
                        'answer_id' => $answer->id,
                        'unit_id' => $unit->id,
                    ]);
                }
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        } finally {
            DB::commit();
        }
    }
}
