<div wire:poll.5s="refreshStats">
    <style>
        .table {
            font-size: .9rem;
        }
    </style>

    <div class="event-info">
        <h1 class="event-title">{{ $event->name }}</h1>
        <p class="event-date mb-0">{{ \Carbon\Carbon::parse($event->date)->tz('Asia/Jakarta')->format('l, d F Y') }}</p>
        <p class="event-date d-inline" wire:ignore><span id="currentTime">{{ now()->tz('Asia/Jakarta')->format('H:i:s') }}</span> WIB</p>
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
                <div>Person</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-4">
            <h3 class="mb-4">Scan to Register</h3>
            <div class="qr-container" wire:ignore>
                <div id="qrcode"></div>
            </div>
            <div class="register-link mt-4">
                <p>Or visit: <a href="{{ route('event.register', $event->id) }}" class="text-info" target="_blank">{{ route('event.register', $event->id) }}</a></p>
            </div>
        </div>
        <div class="col-8">
            <h3 class="mb-4 text-center">Participants</h3>
            <div class="participants-container" id="participantsContainer" style="max-height: 300px; overflow-y: auto;">
                @if (count($approvedParticipants) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th>No</th>
                                    <th>Unit Code</th>
                                    <th>Owner Name</th>
                                    <th>Attendee Name</th>
                                    <th>NPP</th>
                                    <th>Registered At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($approvedParticipants as $index => $unit)
                                    <tr class="participant-row">
                                        <td>{{ $approvedParticipants->count() - $index }}</td>
                                        <td>{{ $unit->pivot->unit_code ?? $unit->code }}</td>
                                        <td>{{ Str::limit($unit->user->name, 50, '...') ?? '-' }}</td>
                                        <td>{{ Str::limit($unit->pivot->attendee_name, 50, '...') ?? '-' }}</td>
                                        <td>{{ number_format($unit->npp, 2) }}%</td>
                                        <td>{{ \Carbon\Carbon::parse($unit->pivot->created_at)->tz('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <p>No approved participants yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('new-participant-added', () => {
                const container = document.getElementById('participantsContainer');
                if (container) {
                    container.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                }
            });
        });

        const eventTime = document.getElementById('currentTime');
        setInterval(() => {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            eventTime.textContent = `${hours}:${minutes}:${seconds}`;
        }, 1000);
    </script>

    <style>
        .participants-container {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background-color: #fff;
        }

        .participant-row {
            transition: background-color 0.3s ease;
        }

        .participant-row:hover {
            background-color: #f8f9fa !important;
        }

        .table thead.sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
        }
    </style>
</div>
