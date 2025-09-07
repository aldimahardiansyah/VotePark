<x-layout.main>
    <form action="{{ route('event.store') }}" method="post">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Event Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Event Date</label>
            <input type="date" class="form-control" id="date" name="date" required>
        </div>

        {{-- submit --}}
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Create Event</button>
        </div>
        {{-- cancel --}}
        <div class="mb-3">
            <a href="{{ route('event.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</x-layout.main>
