<x-layout.main>
    <x-alert.success-and-error />

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center pt-3">
            <h5>Anonymous Voting Sessions</h5>
            <a href="{{ route('anonymous-voting.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> New Voting Session
            </a>
        </div>
        <div class="card-body">
            <table class="table table-striped" id="datatable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Candidates</th>
                        <th>Total Votes</th>
                        <th>Total NPP</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($votingSessions as $session)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $session->title }}</td>
                            <td>{{ $session->date->format('d M Y') }}</td>
                            <td>{{ $session->candidates->count() }}</td>
                            <td>{{ $session->total_votes }}</td>
                            <td>{{ number_format($session->total_npp, 4) }}%</td>
                            <td>
                                @if ($session->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Closed</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="{{ route('anonymous-voting.show', $session->id) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('anonymous-voting.presentation', $session->id) }}" class="btn btn-sm btn-success" target="_blank">Projector</a>
                                    <a href="{{ route('anonymous-voting.edit', $session->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('anonymous-voting.destroy', $session->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this voting session?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layout.main>
