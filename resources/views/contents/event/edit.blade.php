<x-layout.main>
    <form action="{{ route('event.update', $event->id) }}" method="post">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Event Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $event->name }}" required>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Event Date</label>
            <input type="date" class="form-control" id="date" name="date" value="{{ $event->date->format('Y-m-d') }}" required>
        </div>
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="requires_approval" name="requires_approval" value="1" {{ $event->requires_approval ? 'checked' : '' }}>
                <label class="form-check-label" for="requires_approval">
                    Require approval for new participants
                </label>
            </div>
        </div>

        {{-- submit --}}
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Update Event</button>
        </div>
        {{-- cancel --}}
        <div class="mb-3">
            <a href="{{ route('event.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</x-layout.main>
