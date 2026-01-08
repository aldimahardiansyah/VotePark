<div wire:poll.5s="refreshStats">
    <div class="event-info">
        <h1 class="event-title">{{ $event->name }}</h1>
        <p class="event-date">{{ \Carbon\Carbon::parse($event->date)->format('l, d F Y') }}</p>
    </div>

    <div class="row justify-content-center mb-5">
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-number">{{ $registeredUnits }}</div>
                <div>Registered Units</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-number">{{ number_format($totalNpp, 2) }}%</div>
                <div>Total NPP</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-number">{{ $uniqueOwners }}</div>
                <div>Unique Owners</div>
            </div>
        </div>
    </div>

    <div class="text-center">
        <h3 class="mb-4">Scan to Register</h3>
        <div class="qr-container" wire:ignore>
            <div id="qrcode"></div>
        </div>
        <div class="register-link mt-4">
            <p>Or visit: <a href="{{ route('event.register', $event->id) }}" class="text-info" target="_blank">{{ route('event.register', $event->id) }}</a></p>
        </div>
    </div>
</div>
