<x-layout.main>
    <x-alert.success-and-error />

    <div class="row">
        <!-- Voting Session Info -->
        <div class="col-12 col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">{{ $anonymousVoting->title }}</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Date</th>
                            <td>{{ $anonymousVoting->date->format('l, d F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if ($anonymousVoting->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Closed</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Total Candidates</th>
                            <td>{{ $anonymousVoting->candidates->count() }}</td>
                        </tr>
                        <tr>
                            <th>Total Votes</th>
                            <td>{{ $anonymousVoting->total_votes }}</td>
                        </tr>
                        <tr>
                            <th>Total NPP</th>
                            <td>{{ number_format($anonymousVoting->total_npp, 2) }}%</td>
                        </tr>
                    </table>
                    <div class="d-flex gap-2">
                        <a href="{{ route('anonymous-voting.presentation', $anonymousVoting->id) }}" class="btn btn-success btn-sm" target="_blank">
                            <i class="bi bi-display"></i> Presentation
                        </a>
                        <a href="{{ route('anonymous-voting.index') }}" class="btn btn-secondary btn-sm">Back to List</a>
                    </div>
                </div>
            </div>

            <!-- Add Candidate Form -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Add Candidate</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('anonymous-voting.add-candidate', $anonymousVoting->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="sequence_number" class="form-label">Sequence Number</label>
                            <input type="number" class="form-control @error('sequence_number') is-invalid @enderror" id="sequence_number" name="sequence_number" value="{{ old('sequence_number', $anonymousVoting->candidates->count() + 1) }}" min="1" required>
                            @error('sequence_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Candidate Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/*">
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Add Candidate</button>
                    </form>
                </div>
            </div>

            <!-- Candidates List -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Candidates</h6>
                </div>
                <div class="card-body">
                    @if ($anonymousVoting->candidates->count() > 0)
                        @foreach ($anonymousVoting->candidates as $candidate)
                            <div class="card mb-2">
                                <div class="card-body p-2">
                                    <div class="d-flex align-items-center">
                                        @if ($candidate->photo)
                                            <img src="{{ asset('storage/' . $candidate->photo) }}" alt="{{ $candidate->name }}" class="rounded-circle me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-secondary me-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <span class="text-white fw-bold">{{ $candidate->sequence_number }}</span>
                                            </div>
                                        @endif
                                        <div class="flex-grow-1">
                                            <strong>{{ $candidate->sequence_number }}. {{ $candidate->name }}</strong>
                                            <div class="small text-muted">
                                                Votes: {{ $candidate->total_votes }} | NPP: {{ number_format($candidate->total_npp, 2) }}%
                                            </div>
                                        </div>
                                        <form action="{{ route('anonymous-voting.delete-candidate', $candidate->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this candidate?')">×</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No candidates added yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ballot Recording -->
        <div class="col-12 col-md-8">
            @if ($anonymousVoting->is_active && $anonymousVoting->candidates->count() > 0)
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Record Ballot</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('anonymous-voting.record-ballot', $anonymousVoting->id) }}" method="POST" id="ballotForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="voting_candidate_id" class="form-label">Select Candidate</label>
                                    <select class="form-select @error('voting_candidate_id') is-invalid @enderror" id="voting_candidate_id" name="voting_candidate_id" required>
                                        <option value="">-- Select Candidate --</option>
                                        @foreach ($anonymousVoting->candidates as $candidate)
                                            <option value="{{ $candidate->id }}">{{ $candidate->sequence_number }}. {{ $candidate->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('voting_candidate_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="npp" class="form-label">NPP Value</label>
                                    <input type="number" step="0.0001" class="form-control @error('npp') is-invalid @enderror" id="npp" name="npp" value="{{ old('npp') }}" required>
                                    @error('npp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                {{-- <div class="col-md-3 mb-3">
                                    <label for="npp_code" class="form-label">NPP Code (Optional)</label>
                                    <input type="text" class="form-control @error('npp_code') is-invalid @enderror" id="npp_code" name="npp_code" value="{{ old('npp_code') }}">
                                    @error('npp_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div> --}}
                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">Record</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Recorded Ballots -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Recorded Ballots</h6>
                    <span class="badge bg-primary">{{ $anonymousVoting->ballots->count() }} ballots</span>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @if ($anonymousVoting->ballots->count() > 0)
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Candidate</th>
                                    <th>NPP</th>
                                    {{-- <th>NPP Code</th> --}}
                                    <th>Recorded At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($anonymousVoting->ballots->sortByDesc('created_at') as $index => $ballot)
                                    <tr>
                                        <td>{{ $anonymousVoting->ballots->count() - $index }}</td>
                                        <td>{{ $ballot->votingCandidate->sequence_number }}. {{ $ballot->votingCandidate->name }}</td>
                                        <td>{{ number_format($ballot->npp, 2) }}%</td>
                                        {{-- <td>{{ $ballot->npp_code ?? '-' }}</td> --}}
                                        <td>{{ $ballot->created_at->format('H:i:s') }}</td>
                                        <td>
                                            <form action="{{ route('anonymous-voting.delete-ballot', $ballot->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this ballot?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted text-center">No ballots recorded yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layout.main>
