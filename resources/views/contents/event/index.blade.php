<x-layout.main>
    <x-slot name="title">Event</x-slot>
    <x-slot name="header">Event</x-slot>

    <x-alert.success-and-error />

    <div class="flex justify-between mb-4">
        <a href="{{ route('event.create') }}" class="btn btn-primary">Create Event</a>
    </div>

    <x-table :columns="['id', 'name', 'date', 'attendance', 'requires_approval', 'action']">
        @foreach ($events as $event)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $event->name }}</td>
                <td>{{ $event->date->format('d M Y') }}</td>
                <td>{{ $event->approvedUnits->count() }}</td>
                <td>
                    @if ($event->requires_approval)
                        <span class="badge bg-warning">Yes</span>
                    @else
                        <span class="badge bg-secondary">No</span>
                    @endif
                </td>
                <td class="d-flex flex-wrap gap-1">
                    <a href="{{ route('event.show', $event->id) }}" class="btn btn-sm btn-info">View</a>
                    <a href="{{ route('event.edit', $event->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <a href="{{ route('event.presentation', $event->id) }}" class="btn btn-sm btn-success" target="_blank">Presentation</a>

                    {{-- import participant --}}
                    {{-- <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#importParticipantModal{{ $event->id }}">
                        Import Participant
                    </button> --}}

                    <!-- Import Participant Modal -->
                    <div class="modal fade" id="importParticipantModal{{ $event->id }}" tabindex="-1" aria-labelledby="importParticipantModalLabel{{ $event->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('event.import-participant') }}" method="POST" enctype="multipart/form-data">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="importParticipantModalLabel{{ $event->id }}">Import Participant</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        @csrf
                                        <input type="hidden" name="event_id" value="{{ $event->id }}">
                                        <div class="mb-3">
                                            <label for="file" class="form-label">Choose file (xlsx)</label>
                                            <input type="file" class="form-control" id="file" name="file" required>
                                        </div>
                                        <div class="mb-3">
                                            <a href="{{ route('event.participant-template.download', $event->id) }}" class="btn btn-sm btn-outline-secondary">
                                                Download Template
                                            </a>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Import</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('event.destroy', $event->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </x-table>
</x-layout.main>
