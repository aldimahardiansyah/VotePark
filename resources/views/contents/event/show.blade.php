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

    {{-- validation errors --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12 col-md-4">
            <x-event.vote :event="$event" />
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
