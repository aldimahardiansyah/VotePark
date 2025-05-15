<x-layout.main>
    {{-- success --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    {{-- error --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12 col-md-4">
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
                            <th>Participants</th>
                            <td>{{ $event->units->count() }} Person</td>
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
                    <form action="{{ route('event.polling-percentage') }}" method="POST">
                        @csrf
                        <input type="hidden" name="event_id" value="{{ $event->id }}">
                        <div class="mb-3">
                            <label for="file" class="form-label">Import Slido Polling Result</label>
                            <input type="file" class="form-control" id="file" name="file" required>
                        </div>
                        <button type="submit" class="btn btn-primary d-block mx-auto">Calculate</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center pt-3">
                    <h5 class="text-center">Participants</h5>
                    <x-event.add-participant-modal :event="$event" />
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="datatable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>NPP</th>
                                <th>Unit</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($event->units as $unit)
                                <tr>
                                    <td>{{ $unit->user->name }}</td>
                                    <td>{{ $unit->npp ?? '-' }}</td>
                                    <td>{{ $unit->code }}</td>
                                    <td>{{ $unit->user->email }}</td>
                                    <td>
                                        <form action="{{ route('event.remove-participant') }}" method="POST">
                                            @csrf
                                            @method('delete')
                                            <input type="hidden" name="event_id" value="{{ $event->id }}">
                                            <input type="hidden" name="unit_id" value="{{ $unit->id }}">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
</x-layout.main>
