<x-layout.main>
    <x-alert.success-and-error />

    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center pt-3">
                <h5 class="text-center">Rejected Participants - {{ $event->name }}</h5>
                <a href="{{ route('event.show', $event->id) }}" class="btn btn-secondary">Back to Event</a>
            </div>
            <div class="card-body">
                @if($event->rejectedUnits->count() === 0)
                    <div class="alert alert-info">No rejected participants.</div>
                @else
                    <table class="table table-striped" id="datatable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>NPP</th>
                                <th>Unit</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($event->rejectedUnits as $unit)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $unit->user->name }}</td>
                                    <td>{{ $unit->npp ?? '-' }}</td>
                                    <td>{{ $unit->code }}</td>
                                    <td>{{ $unit->pivot->registered_email ?? $unit->user->email }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <form action="{{ route('event.approve-participant') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="event_id" value="{{ $event->id }}">
                                                <input type="hidden" name="unit_id" value="{{ $unit->id }}">
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form action="{{ route('event.remove-participant') }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('delete')
                                                <input type="hidden" name="event_id" value="{{ $event->id }}">
                                                <input type="hidden" name="unit_id" value="{{ $unit->id }}">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Remove</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-layout.main>
