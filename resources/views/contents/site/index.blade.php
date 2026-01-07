<x-layout.main>
    <x-slot name="title">Sites</x-slot>
    <x-slot name="header">Sites</x-slot>

    <div class="container">
        <x-alert.success-and-error />

        <div class="row mb-3">
            <div class="col-md-6">
                <h1>Sites Management</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('site.create') }}" class="btn btn-primary">Add Site</a>
            </div>
        </div>

        @if ($sites->isEmpty())
            <div class="alert alert-info">No sites found.</div>
        @else
            <table class="table table-bordered" id="datatable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Domain</th>
                        <th>Users</th>
                        <th>Units</th>
                        <th>Events</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sites as $site)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $site->name }}</td>
                            <td>{{ $site->slug }}</td>
                            <td>{{ $site->domain ?? '-' }}</td>
                            <td>{{ $site->users_count }}</td>
                            <td>{{ $site->units_count }}</td>
                            <td>{{ $site->events_count }}</td>
                            <td>
                                @if($site->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="d-flex gap-1">
                                <a href="{{ route('site.show', $site->id) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('site.edit', $site->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('site.destroy', $site->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layout.main>
