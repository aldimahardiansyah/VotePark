<x-layout.main>
    <x-slot name="title">Event</x-slot>
    <x-slot name="header">Event</x-slot>

    <div class="flex justify-between mb-4">
        <a href="{{ route('event.create') }}" class="btn btn-primary">Create Event</a>
    </div>

    <x-table :columns="['id', 'name', 'date', 'attendance', 'action']">
        @foreach ($events as $event)
            <tr>
                <td>{{ $event->id }}</td>
                <td>{{ $event->name }}</td>
                <td>{{ $event->date }}</td>
                <td>{{ $event->units->count() }}</td>
                <td class="d-flex">
                    <a href="{{ route('event.show', $event->id) }}" class="btn btn-info mx-1">View</a>
                    {{-- <a href="{{ route('event.edit', $event->id) }}" class="btn btn-warning">Edit</a>
                    <form action="{{ route('event.destroy', $event->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form> --}}

                    {{-- import attendance --}}
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importAttendnaceModal">
                        Import Attendance
                    </button>

                    <!-- Import Unit Modal -->
                    <div class="modal fade" id="importAttendnaceModal" tabindex="-1" aria-labelledby="importAttendnaceModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('event.import-attendance') }}" method="POST" enctype="multipart/form-data">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="importAttendnaceModalLabel">Import Attendance Email</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="file" class="form-label">Choose file (xlsx)</label>
                                            <input type="file" class="form-control" id="file" name="file" required>
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
                </td>
            </tr>
        @endforeach
    </x-table>
</x-layout.main>
