<x-layout.main>
    <x-alert.success-and-error />

    <div class="card">
        <div class="card-header">
            <h5>Create Voting Session</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('anonymous-voting.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control @error('date') is-invalid @enderror" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if ($sites->count() > 0)
                    <div class="mb-3">
                        <label for="site_id" class="form-label">Site</label>
                        <select class="form-select @error('site_id') is-invalid @enderror" id="site_id" name="site_id">
                            <option value="">-- Select Site --</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}" {{ old('site_id') == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                            @endforeach
                        </select>
                        @error('site_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="d-flex justify-content-between">
                    <a href="{{ route('anonymous-voting.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Voting Session</button>
                </div>
            </form>
        </div>
    </div>
</x-layout.main>
