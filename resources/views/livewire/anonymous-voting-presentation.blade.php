<div wire:poll.3s="refreshStats">
    <style>
        .candidate-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin: 10px;
            transition: transform 0.3s ease;
        }

        .candidate-card:hover {
            transform: scale(1.02);
        }

        .candidate-photo {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #14b8a6;
            margin-bottom: 15px;
        }

        .candidate-photo-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #14b8a6;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            color: white;
            margin-left: auto;
            margin-right: auto;
        }

        .candidate-name {
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .candidate-number {
            font-size: 1rem;
            opacity: 0.8;
            margin-bottom: 15px;
        }

        .vote-count {
            font-size: 2.5rem;
            font-weight: bold;
            color: #14b8a6;
        }

        .vote-label {
            font-size: 0.9rem;
            opacity: 0.7;
        }

        .leaderboard-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            transition: background 0.3s ease;
        }

        .leaderboard-item:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .leaderboard-rank {
            font-size: 2rem;
            font-weight: bold;
            width: 60px;
            text-align: center;
        }

        .leaderboard-rank.gold {
            color: #ffd700;
        }

        .leaderboard-rank.silver {
            color: #c0c0c0;
        }

        .leaderboard-rank.bronze {
            color: #cd7f32;
        }

        .leaderboard-photo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 15px;
        }

        .leaderboard-photo-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }

        .leaderboard-info {
            flex-grow: 1;
        }

        .leaderboard-name {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .leaderboard-votes {
            text-align: right;
            font-size: 1.5rem;
            font-weight: bold;
            color: #14b8a6;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }

        .stats-row {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 40px;
            margin-bottom: 30px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: #14b8a6;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.8;
        }
    </style>

    <div class="event-info text-center">
        <h1 class="event-title" style="font-size: 2.5rem; font-weight: bold;">{{ $votingSession->title }}</h1>
        <p class="event-date mb-0" style="font-size: 1.3rem; opacity: 0.8;">{{ \Carbon\Carbon::parse($votingSession->date)->tz('Asia/Jakarta')->format('l, d F Y') }}</p>
        <p class="event-date d-inline" style="font-size: 1.1rem; opacity: 0.8;" wire:ignore>
            <span id="currentTime">{{ now()->tz('Asia/Jakarta')->format('H:i:s') }}</span> WIB
        </p>
    </div>

    <!-- Summary Stats -->
    <div class="stats-row">
        <div class="stat-item">
            <div class="stat-value">{{ $totalVotes }}</div>
            <div class="stat-label">Total Votes</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ number_format($totalNpp, 2) }}%</div>
            <div class="stat-label">Total NPP</div>
        </div>
    </div>

    <div class="row">
        <!-- Candidates with Results -->
        <div class="col-lg-8 mb-4">
            <h3 class="section-title">Candidates</h3>
            <div class="row justify-content-center">
                @forelse ($candidates as $candidate)
                    <div class="col-md-{{ count($candidates) <= 3 ? '4' : '4' }} mb-3">
                        <div class="candidate-card">
                            @if ($candidate['photo'])
                                <img src="{{ asset('storage/' . $candidate['photo']) }}" alt="{{ $candidate['name'] }}" class="candidate-photo">
                            @else
                                <div class="candidate-photo-placeholder">{{ $candidate['sequence_number'] }}</div>
                            @endif
                            <div class="candidate-number">No. Urut {{ $candidate['sequence_number'] }}</div>
                            <div class="candidate-name">{{ $candidate['name'] }}</div>
                            <div class="mt-3">
                                <div class="vote-count">{{ number_format($candidate['total_npp'], 2) }}%</div>
                                {{-- <div class="vote-label">NPP</div> --}}
                            </div>
                            {{-- <div class="mt-2">
                                <div class="vote-count" style="font-size: 1.5rem;">{{ $candidate['total_votes'] }}</div>
                                <div class="vote-label">Suara</div>
                            </div> --}}
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center">
                        <p class="text-muted">No candidates registered yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Leaderboard -->
        <div class="col-lg-4">
            <h3 class="section-title">Leaderboard</h3>
            <div class="leaderboard-container" style="max-height: 550px; overflow-y: auto;">
                @forelse ($leaderboard as $index => $candidate)
                    <div class="leaderboard-item">
                        {{-- <div class="leaderboard-rank {{ $index === 0 ? 'gold' : ($index === 1 ? 'silver' : ($index === 2 ? 'bronze' : '')) }}">
                            @if ($index === 0)
                                👑 {{ $index + 1 }}
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div> --}}
                        @if ($candidate['photo'])
                            <img src="{{ asset('storage/' . $candidate['photo']) }}" alt="{{ $candidate['name'] }}" class="leaderboard-photo">
                        @else
                            <div class="leaderboard-photo-placeholder">{{ $candidate['sequence_number'] }}</div>
                        @endif
                        <div class="leaderboard-info">
                            <div class="leaderboard-name">{{ $candidate['name'] }}</div>
                            <div class="small opacity-75">No. Urut {{ $candidate['sequence_number'] }}</div>
                        </div>
                        <div class="leaderboard-votes">
                            <div>{{ number_format($candidate['total_npp'], 2) }}%</div>
                            <div class="small opacity-75">{{ $candidate['total_votes'] }} suara</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <p>No results yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('new-vote-added', () => {
                // Play a sound or show notification
                console.log('New vote added!');
            });
        });

        const eventTime = document.getElementById('currentTime');
        if (eventTime) {
            setInterval(() => {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                eventTime.textContent = `${hours}:${minutes}:${seconds}`;
            }, 1000);
        }
    </script>
</div>
