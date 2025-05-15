<x-layout.main>
    <x-slot name="title">Vote Results</x-slot>

    <div class="row">
        <div class="col-12 col-md-6">
            <table class="table">
                <tr>
                    <td>Version</td>
                    <td>
                        : Vote #{{ $vote->id }}
                    </td>
                </tr>
                <tr>
                    <td>Event</td>
                    <td>
                        : <a href="{{ route('event.show', $vote->event_id) }}">{{ $vote->event->name }}</a>
                    </td>
                </tr>
                <tr>
                    <td>Participants</td>
                    <td>
                        : {{ $userParticipants->count() }} Person / {{ $participants->count() }} Units
                    </td>
                </tr>
                <tr>
                    <td>Participants NPP</td>
                    <td>
                        : {{ round($participants->sum('npp'), 2) }}%
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <hr>

    @foreach ($questions as $question)
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mt-2">{{ $question->desc }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="w-75">Option</th>
                            <th>Votes (NPP)</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalNPPVotes = 0;
                            $participantsNPP = $participants->sum('npp');
                        @endphp
                        @foreach ($question->answers as $answer)
                            @php
                                $nppVotes = $answer->units->sum('npp');
                                $totalNPPVotes += $nppVotes;
                            @endphp
                            <tr>
                                <td>{{ $answer->desc }}</td>
                                <td>{{ $nppVotes }}</td>
                                <td>{{ number_format(($nppVotes / $participantsNPP) * 100, 2) }}%</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="text-danger">Tidak memilih</td>
                            <td class="text-danger">{{ $participantsNPP - $totalNPPVotes }}</td>
                            <td class="text-danger">{{ number_format((($participantsNPP - $totalNPPVotes) / $participantsNPP) * 100, 2) }}%</td>
                        </tr>
                    </tbody>
                </table>

                <div class="text-end">
                    <a href="{{ route('question.show', $question->id) }}" class="btn btn-link">View Details ></a>
                </div>
            </div>
        </div>
    @endforeach
</x-layout.main>
