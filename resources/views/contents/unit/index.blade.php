<x-layout.main>
    <x-slot name="title">Units</x-slot>
    <x-slot name="header">Units</x-slot>

    <div class="container">
        <div class="row mb-3">
            <div class="col-md-6">
                <h1>Units</h1>
            </div>
            <div class="col-md-6 text-end">
                {{-- <a href="{{ route('unit.create') }}" class="btn btn-primary">Create Unit</a> --}}
                {{-- Import unit --}}

                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importUnitModal">
                    Import Unit
                </button>
            </div>
        </div>

        <!-- Import Unit Modal -->
        <div class="modal fade" id="importUnitModal" tabindex="-1" aria-labelledby="importUnitModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('unit.import') }}" method="POST" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="importUnitModalLabel">Import Unit</h1>
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

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($units->isEmpty())
            <p>No units found.</p>
        @else
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Code</th>
                        <th>NPP</th>
                        <th>Luas</th>
                        <th>Tenant</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($units as $unit)
                        <tr>
                            <td>{{ $loop->iteration + ($units->currentPage() - 1) * $units->perPage() }}</td>
                            <td>{{ $unit->code }}</td>
                            <td>{{ $unit->npp ?? '-' }}</td>
                            <td>{{ $unit->wide ?? '-' }}</td>
                            <td>{{ $unit->user->name ?? '-' }}</td>
                            <td>{{ $unit->user->email ?? '-' }}</td>
                            <td>
                                <a href="{{ route('unit.edit', $unit->id) }}" class="btn btn-warning">Edit</a>
                                <form action="{{ route('unit.destroy', $unit->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $units->links() }}
        @endif
    </div>
</x-layout.main>
