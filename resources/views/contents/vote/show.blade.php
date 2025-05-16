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
                <div class="d-flex justify-content-between">
                    <h5 class="mt-2">{{ $question->desc }}</h5>

                    <form action="{{ route('question.update', $question->id) }}" method="post">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="question_id" value="{{ $question->id }}">
                        <div class="mb-3">
                            <label for="question" class="form-label small">Calculate based on NPP?</label>
                            <select class="form-select form-select-sm npp_based" name="is_npp_based">
                                <option value="1" {{ $question->is_npp_based ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ !$question->is_npp_based ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="w-75">Option</th>
                            <th>Votes {{ $question->is_npp_based ? '(NPP)' : '(Man)' }}</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalVotes = 0;
                            $participantsCount = $question->is_npp_based ? $participants->sum('npp') : $participants->pluck('user')->unique()->count();
                        @endphp
                        @foreach ($question->answers as $answer)
                            @php
                                $votes = $answer->units->sum('npp');
                                if (!$question->is_npp_based) {
                                    $votes = $answer->units->pluck('user')->unique()->count();
                                }

                                $totalVotes += $votes;
                            @endphp
                            <tr>
                                <td>{{ $answer->desc }}</td>
                                <td>{{ $votes }}</td>
                                <td>{{ number_format(($votes / $participantsCount) * 100, 2) }}%</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="text-danger">Not Answered</td>
                            <td class="text-danger">{{ $participantsCount - $totalVotes }}</td>
                            <td class="text-danger">{{ number_format((($participantsCount - $totalVotes) / $participantsCount) * 100, 2) }}%</td>
                        </tr>
                    </tbody>
                </table>

                <div class="text-end">
                    <a href="{{ route('question.show', $question->id) }}" class="btn btn-link">View Details ></a>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        document.querySelectorAll('.npp_based').forEach(function(select) {
            select.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });
    </script>
</x-layout.main>
