<x-layout.main>
    <x-slot name="title">Site Details</x-slot>
    <x-slot name="header">Site Details</x-slot>

    <div class="container">
        <div class="row mb-3">
            <div class="col-md-6">
                <h1>{{ $site->name }}</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('site.edit', $site->id) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('site.index') }}" class="btn btn-secondary">Back to Sites</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Site Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Slug</strong></td>
                                <td>{{ $site->slug }}</td>
                            </tr>
                            <tr>
                                <td><strong>Domain</strong></td>
                                <td>{{ $site->domain ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Description</strong></td>
                                <td>{{ $site->description ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status</strong></td>
                                <td>
                                    @if($site->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Created</strong></td>
                                <td>{{ $site->created_at->format('d M Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card text-center mb-4">
                            <div class="card-body">
                                <h2 class="text-primary">{{ $site->users->count() }}</h2>
                                <p class="mb-0">Users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center mb-4">
                            <div class="card-body">
                                <h2 class="text-success">{{ $site->units->count() }}</h2>
                                <p class="mb-0">Units</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center mb-4">
                            <div class="card-body">
                                <h2 class="text-info">{{ $site->events->count() }}</h2>
                                <p class="mb-0">Events</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($site->events->count() > 0)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Events</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                @foreach($site->events->take(5) as $event)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $event->name }}
                                        <span class="badge bg-primary">{{ $event->date->format('d M Y') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout.main>
