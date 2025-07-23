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

class VoteGFormImport implements ToCollection
{
    protected $eventId;
    protected $event;

    public function __construct($eventId)
    {
        $this->eventId = $eventId;
        $this->event = Event::with('units.user')->findOrFail($eventId);
    }

    public function collection(Collection $collection)
    {
        if (!$this->isValidHeader($collection[0])) {
            return redirect()->back()->with('error', 'Invalid file format. Please check the file and try again.');
        }

        $questionMap = $this->extractQuestions($collection[0]);
        if (empty($questionMap)) {
            return redirect()->back()->with('error', 'No valid questions found in the file.');
        }

        DB::beginTransaction();

        try {
            $vote = $this->createVote();

            $questionIds = $this->createQuestions($vote->id, $questionMap);

            foreach ($collection->skip(1) as $row) {
                $this->processParticipantRow($row, $vote, $questionIds);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    protected function isValidHeader($headerRow): bool
    {
        return isset($headerRow[1]) && $headerRow[1] === 'Email Address';
    }

    protected function extractQuestions($headerRow): array
    {
        $questions = [];

        foreach ($headerRow as $index => $text) {
            if ($index >= 2 && !empty($text)) {
                $questions[$index] = trim($text);
            }
        }

        return $questions;
    }

    protected function createVote(): Vote
    {
        return Vote::create([
            'event_id' => $this->eventId,
            'name' => now(),
        ]);
    }

    protected function createQuestions($voteId, array $questionMap): array
    {
        $questionIds = [];

        foreach ($questionMap as $colIndex => $questionText) {
            $question = Question::firstOrCreate([
                'vote_id' => $voteId,
                'desc' => $questionText,
            ]);

            $questionIds[$colIndex] = $question->id;
        }

        return $questionIds;
    }

    // protected function processParticipantRow($row, Vote $vote, array $questionIds): void
    // {
    //     $email = $row[1] ?? null;
    //     if (!$email) return;

    //     $user = User::where('email', $email)->first();
    //     if (!$user) {
    //         Log::error("Participant not found: {$email}");
    //         return;
    //     }

    //     $units = $this->event->units->filter(function ($unit) use ($email) {
    //         return $unit->user && $unit->user->email === $email;
    //     });

    //     if ($units->isEmpty()) {
    //         Log::error("User is not on event: {$email}");
    //         return;
    //     }

    //     foreach ($questionIds as $colIndex => $questionId) {
    //         $answerText = $row[$colIndex] ?? null;
    //         if (empty($answerText)) continue;

    //         $answer = Answer::updateOrCreate(
    //             ['question_id' => $questionId, 'desc' => $answerText],
    //             [
    //                 'votes' => DB::raw('votes + 1'),
    //                 'npp_votes' => DB::raw('npp_votes + ' . $units->sum('npp')),
    //             ]
    //         );

    //         foreach ($units as $unit) {
    //             $answer->units()->attach($unit->id, [
    //                 'vote_id' => $vote->id,
    //                 'question_id' => $questionId,
    //                 'answer_id' => $answer->id,
    //                 'unit_id' => $unit->id,
    //             ]);
    //         }
    //     }
    // }

    protected function processParticipantRow($row, Vote $vote, array $questionIds): void
    {
        $email = $row[1] ?? null;
        if (!$email) return;

        $user = User::where('email', $email)->first();
        if (!$user) {
            Log::error("Participant not found: {$email}");
            return;
        }

        $units = $this->event->units->filter(function ($unit) use ($email) {
            return $unit->user && $unit->user->email === $email;
        });

        if ($units->isEmpty()) {
            Log::error("User is not on event: {$email}");
            return;
        }

        foreach ($questionIds as $colIndex => $questionId) {
            $answerText = $row[$colIndex] ?? null;
            if (empty($answerText)) continue;

            foreach ($units as $unit) {
                $unitId = $unit->id;

                // CEK apakah unit sudah menjawab question ini di DB (pivot sudah ada)
                $hasAnswered = DB::table('answer_units')
                    ->where('question_id', $questionId)
                    ->where('unit_id', $unitId)
                    ->exists();

                if ($hasAnswered) {
                    Log::info("Unit {$unitId} has already answered question {$questionId}, skipping...");
                    continue;
                }

                // Simpan/jawab
                $answer = Answer::updateOrCreate(
                    ['question_id' => $questionId, 'desc' => $answerText],
                    [
                        'votes' => DB::raw('votes + 1'),
                        'npp_votes' => DB::raw('npp_votes + ' . $unit->npp),
                    ]
                );

                $answer->units()->attach($unitId, [
                    'vote_id' => $vote->id,
                    'question_id' => $questionId,
                    'answer_id' => $answer->id,
                    'unit_id' => $unitId,
                ]);
            }
        }
    }
}
