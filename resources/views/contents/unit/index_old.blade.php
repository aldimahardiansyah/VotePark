<x-layout.main>
    <x-slot name="title">Units</x-slot>
    <x-slot name="header">Units</x-slot>

    <div class="container">
        <div class="card mb-4 shadow-sm">
            <div class="card-header text-white" style="background-color: #14b8a6;">
                <h5 class="mb-0">Master Data Summary</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover  mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Tower</th>
                                <th scope="col" class="text-end">Jumlah Unit</th>
                                <th scope="col" class="text-end">Total NPP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($towers as $tower => $data)
                                <tr>
                                    <td><strong>{{ 'Tower ' . $tower ?? '-' }}</strong></td>
                                    <td class="text-end">{{ $data['count'] }}</td>
                                    <td class="text-end">{{ number_format($data['total_npp'], 2) }}</td>
                                </tr>
                            @endforeach
                            @php
                                $totalCount = $towers->sum(fn($data) => $data['count']);
                                $totalNpp = $towers->sum(fn($data) => $data['total_npp']);
                            @endphp

                            <tr class="table-dark fw-bold">
                                <td>Total</td>
                                <td class="text-end">{{ $totalCount }}</td>
                                <td class="text-end">{{ number_format($totalNpp, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <x-alert.success-and-error />

        <div class="row mb-3">
            <div class="col-md-6">
                <h1>Units</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('unit.create', ['password=' . request('password')]) }}" class="btn btn-primary">Add Unit</a>
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
            <table class="table table-bordered" id="datatable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Code</th>
                        <th>NPP</th>
                        <th>Tower</th>
                        {{-- <th>Luas</th> --}}
                        <th>Tenant</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($units as $unit)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $unit->code }}</td>
                            <td>{{ $unit->npp ?? '-' }}</td>
                            <td>Tower{{ $unit->tower ?? '-' }}</td>
                            {{-- <td>{{ $unit->wide ?? '-' }}</td> --}}
                            <td>{{ $unit->user->name ?? '-' }}</td>
                            <td>{{ $unit->user->email ?? '-' }}</td>
                            <td class="d-flex">
                                <a href="{{ route('unit.edit', [$unit->id, 'password=' . request('password')]) }}" class="btn btn-warning me-2">Edit</a>
                                <form action="{{ route('unit.destroy', [$unit->id, 'password=' . request('password')]) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layout.main>
