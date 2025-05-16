<x-layout.main>
    <x-slot name="title">Question Detail</x-slot>

    <div class="row">
        <div class="col-12">
            <table class="table">
                <tr>
                    <td>
                        <h4>Question</h4>
                    </td>
                    <td>
                        <h4>: {{ $question->desc }}</h4>
                    </td>
                </tr>

                <tr>
                    <td>
                        <h5>Voting Type</h5>
                    </td>
                    <td>
                        <h5>: {{ $question->is_npp_based ? 'NPP Based' : '1 Man 1 Vote' }}</h5>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <hr>

    @php
        $isNppBased = $question->is_npp_based;
        $answers = $question->answers;
        $totalVotes = 0;
        $answeredUnits = [];
    @endphp

    @foreach ($answers as $answer)
        @php
            $totalVotes += $answer->units->sum('npp');
            $answeredUnits = array_merge($answeredUnits, $answer->units->pluck('id')->toArray());
        @endphp

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mt-2">
                    {{ $answer->desc }}
                    <span class="badge bg-success">
                        @if ($isNppBased)
                            {{ ($answer->units->sum('npp') / $participants->sum('npp')) * 100 }}%
                        @else
                            {{ ($answer->units->pluck('user')->unique()->count() / $participants->pluck('user')->unique()->count()) * 100 }}%
                        @endif
                    </span>
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    @if ($isNppBased)
                        <thead>
                            <tr>
                                <th class="w-50">Residents</th>
                                <th>Unit</th>
                                <th>NPP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($answer->units as $unit)
                                <tr>
                                    <td>{{ $unit->user->name }}</td>
                                    <td>{{ $unit->code }}</td>
                                    <td>{{ $unit->npp }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td class="fw-bold">Total</td>
                                <td class="fw-bold">{{ $answer->units->count() }} Unit</td>
                                <td class="fw-bold">{{ $answer->units->sum('npp') }}</td>
                            </tr>
                        </tbody>
                    @else
                        <thead>
                            <tr>
                                <th class="w-50">Residents</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($answer->units->pluck('user')->unique() as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td class="fw-bold">Total</td>
                                <td class="fw-bold">{{ $answer->units->pluck('user')->unique()->count() }} Residents</td>
                            </tr>
                        </tbody>
                    @endif
                </table>
            </div>
        </div>
    @endforeach

    @php
        $notAnswered = $participants->whereNotIn('id', $answeredUnits);
    @endphp

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mt-2 text-danger">
                Not Answered
                <span class="badge bg-danger">
                    @if ($isNppBased)
                        {{ ($notAnswered->sum('npp') / $participants->sum('npp')) * 100 }}%
                    @else
                        {{ ($notAnswered->pluck('user')->unique()->count() / $participants->pluck('user')->unique()->count()) * 100 }}%
                    @endif
                </span>
            </h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                @if ($isNppBased)
                    <thead>
                        <tr>
                            <th class="w-50">Residents</th>
                            <th>Unit</th>
                            <th>NPP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notAnswered as $unit)
                            <tr>
                                <td>{{ $unit->user->name }}</td>
                                <td>{{ $unit->code }}</td>
                                <td>{{ $unit->npp }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="fw-bold">Total</td>
                            <td class="fw-bold">{{ $notAnswered->count() }} Unit</td>
                            <td class="fw-bold">{{ $notAnswered->sum('npp') }}</td>
                        </tr>
                    </tbody>
                @else
                    <thead>
                        <tr>
                            <th class="w-50">Residents</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notAnswered->pluck('user')->unique() as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="fw-bold">Total</td>
                            <td class="fw-bold">{{ $notAnswered->pluck('user')->unique()->count() }} Residents</td>
                        </tr>
                    </tbody>
                @endif
            </table>
        </div>
    </div>
</x-layout.main>
