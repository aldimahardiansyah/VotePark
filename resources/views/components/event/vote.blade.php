@php
    $towers = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
    $approvedUnits = $event->approvedUnits;
@endphp
<div class="card">
    <div class="card-header">
        <h5 class="text-center mt-3">Event Details</h5>
    </div>
    <div class="card-body">
        <table class="table">
            <tr>
                <th>Name</th>
                <td>{{ $event->name }}</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>{{ \Carbon\Carbon::parse($event->date)->format('l, d F Y') }}</td>
            </tr>
            <tr>
                <th>Requires Approval</th>
                <td>
                    @if($event->requires_approval)
                        <span class="badge bg-warning">Yes</span>
                    @else
                        <span class="badge bg-secondary">No</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Total Participants</th>
                <td>
                    {{ $approvedUnits->pluck('user')->unique()->count() }} Person / {{ $approvedUnits->count() }} Unit
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    @foreach ($towers as $tower)
                        @if ($approvedUnits->where('tower', $tower)->count() > 0)
                            <div class="border-bottom">
                                -<span class="ms-2 small text-secondary">Tower {{ $tower }}: &nbsp;{{ $approvedUnits->where('tower', $tower)->count() }} Unit</span>
                            </div>
                        @endif
                    @endforeach
                </td>
            </tr>
            <tr>
                <th>Total NPP</th>
                <td>{{ $approvedUnits->sum('npp') }}%</td>
            </tr>
            <tr>
                <th>Created At</th>
                <td>{{ $event->created_at }}</td>
            </tr>
        </table>
    </div>
</div>

<div class="mt-3 card">
    <div class="card-header">
        <h6 class="mt-2">Polling Percentage By Participants NPP</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('vote.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="event_id" value="{{ $event->id }}">
            <div class="mb-3">
                <label for="file" class="form-label">Import <a href="{{ asset('polling-result/slido.xlsx') }}">Slido</a> Polling Result</label>
                <input type="file" class="form-control" id="file" name="file">
            </div>

            <div class="mb-3 text-center">
                {{-- Or --}}
                {{-- devider --}}
                <div class="d-flex align-items-center justify-content-center">
                    <hr class="w-25">
                    <span class="mx-2">Or</span>
                    <hr class="w-25">
                </div>
            </div>

            <div class="mb-4">
                <label for="file-gform" class="form-label">Import <a href="{{ asset('polling-result/gfrom.xlsx') }}">Google Form</a> Polling Result</label>
                <input type="file" class="form-control" id="file-gform" name="file-gform">
            </div>

            <button type="submit" class="btn btn-primary d-block mx-auto">Import</button>
        </form>

        <hr>

        {{-- list vote imported --}}
        @foreach ($event->votes as $vote)
            <a href="{{ route('vote.show', $vote->id) }}" class="text-decoration-none">
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8">
                                <span class="h5 text-primary">Vote #{{ $vote->id }}</span>
                                <br>
                                <small class="text-secondary">{{ $vote->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="col-4">
                                <div class="d-flex justify-content-end align-items-center h-100">
                                    {{-- delete vote --}}
                                    <form action="{{ route('vote.destroy', $vote->id) }}" method="POST">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
