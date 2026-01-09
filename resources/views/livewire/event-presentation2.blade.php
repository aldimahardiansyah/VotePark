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

    <div>
        <h3 class="mb-4 text-center">Participants</h3>
        <div class="participants-container" id="participantsContainer" style="max-height: 500px; overflow-y: auto;">
            @if (count($approvedParticipants) > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
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
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $unit->pivot->unit_code ?? $unit->code }}</td>
                                    <td>{{ $unit->user->name ?? '-' }}</td>
                                    <td>{{ $unit->pivot->attendee_name ?? '-' }}</td>
                                    <td>{{ number_format($unit->npp, 2) }}%</td>
                                    <td>{{ \Carbon\Carbon::parse($unit->pivot->created_at)->format('d/m/Y H:i') }}</td>
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
