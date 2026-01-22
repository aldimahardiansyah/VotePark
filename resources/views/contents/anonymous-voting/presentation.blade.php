<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $votingSession->title }} - Live Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    @livewireStyles
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            min-height: 100vh;
        }

        .fullscreen-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .timer-btn {
            position: fixed;
            top: 20px;
            right: 160px;
            z-index: 1000;
        }

        .event-info {
            text-align: center;
            padding: 30px;
        }

        .event-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .event-date {
            font-size: 1.5rem;
            opacity: 0.8;
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin: 10px;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #14b8a6;
        }

        /* Speech Timer Styles */
        .timer-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .timer-modal.active {
            display: flex;
        }

        .timer-display {
            font-size: 15rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            color: #14b8a6;
            text-shadow: 0 0 30px rgba(20, 184, 166, 0.5);
            transition: color 0.3s ease;
        }

        .timer-display.warning {
            color: #f59e0b;
            text-shadow: 0 0 30px rgba(245, 158, 11, 0.5);
        }

        .timer-display.danger {
            color: #ef4444;
            text-shadow: 0 0 30px rgba(239, 68, 68, 0.5);
            animation: pulse 1s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .timer-candidate-name {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .timer-controls {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .timer-controls button {
            padding: 15px 40px;
            font-size: 1.3rem;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .timer-controls button:hover {
            transform: scale(1.05);
        }

        .timer-settings {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.1);
            padding: 20px 30px;
            border-radius: 15px;
            display: none;
            gap: 20px;
            align-items: center;
        }

        .timer-settings label {
            color: white;
            margin-right: 5px;
        }

        .timer-settings input,
        .timer-settings select {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            width: 80px;
        }

        .timer-settings select {
            width: 180px;
        }

        .close-timer {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ef4444;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
        }
    </style>
</head>

<body>
    <button class="btn btn-warning timer-btn" onclick="openTimerModal()">
        <span>⏱️ Timer</span>
    </button>
    <button class="btn btn-light fullscreen-btn" onclick="toggleFullscreen()">
        <span id="fullscreen-icon">Fullscreen</span>
    </button>

    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-11">
                @livewire('anonymous-voting-presentation', ['votingSession' => $votingSession])
            </div>
        </div>
    </div>

    <!-- Speech Timer Modal -->
    <div class="timer-modal" id="timerModal">
        <button class="close-timer" onclick="closeTimerModal()">✕ Tutup Timer</button>

        <div class="timer-candidate-name" id="timerCandidateName">Timer</div>
        <div class="timer-display" id="timerDisplay">05:00</div>

        <div class="timer-controls">
            <button id="startPauseBtn" class="btn btn-success" onclick="toggleTimer()">▶ Mulai</button>
            <button class="btn btn-danger" onclick="resetTimer()">↺ Reset</button>
            <button class="btn btn-info" onclick="toggleTimerSettings()">⚙️ Pengaturan</button>
        </div>

        <div class="timer-settings">
            <div>
                <label for="timerCandidate">Calon:</label>
                <select id="timerCandidate" onchange="updateCandidateName()">
                    <option value="">-- Pilih Calon --</option>
                    @foreach ($votingSession->candidates as $candidate)
                        <option value="{{ $candidate->sequence_number }}. {{ $candidate->name }}">
                            {{ $candidate->sequence_number }}. {{ $candidate->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="timerMinutes">Menit:</label>
                <input type="number" id="timerMinutes" value="5" min="0" max="60" onchange="updateTimerFromSettings()">
            </div>
            <div>
                <label for="timerSeconds">Detik:</label>
                <input type="number" id="timerSeconds" value="0" min="0" max="59" onchange="updateTimerFromSettings()">
            </div>
            <div>
                <label for="soundWarning">Suara Peringatan:</label>
                <select id="soundWarning">
                    <option value="10,9,8,7,6,5,4,3,2,1">10-1 detik</option>
                    <option value="5,4,3,2,1">5-1 detik</option>
                    <option value="30,10,5">30, 10, 5 detik</option>
                    <option value="10,5">10 dan 5 detik</option>
                    <option value="5">5 detik saja</option>
                    <option value="none">Tidak ada suara</option>
                </select>
            </div>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="soundEnabled" checked>
                <label class="form-check-label" for="soundEnabled">Suara Aktif</label>
            </div>
        </div>
    </div>

    @livewireScripts
    <script>
        // Fullscreen toggle
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                document.getElementById('fullscreen-icon').textContent = 'Exit Fullscreen';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                    document.getElementById('fullscreen-icon').textContent = 'Fullscreen';
                }
            }
        }

        // Speech Timer Variables
        let timerInterval = null;
        let remainingSeconds = 300; // 5 minutes default
        let isRunning = false;
        let warningSounds = [30, 10, 5];
        let playedWarnings = new Set();

        // Audio context for beep sounds
        let audioContext = null;

        function getAudioContext() {
            if (!audioContext) {
                audioContext = new(window.AudioContext || window.webkitAudioContext)();
            }
            return audioContext;
        }

        function playBeep(frequency = 800, duration = 200, type = 'sine') {
            if (!document.getElementById('soundEnabled').checked) return;

            try {
                const ctx = getAudioContext();
                const oscillator = ctx.createOscillator();
                const gainNode = ctx.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(ctx.destination);

                oscillator.frequency.value = frequency;
                oscillator.type = type;

                gainNode.gain.setValueAtTime(0.5, ctx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + duration / 1000);

                oscillator.start(ctx.currentTime);
                oscillator.stop(ctx.currentTime + duration / 1000);
            } catch (e) {
                console.log('Audio not available');
            }
        }

        function playWarningBeep() {
            playBeep(600, 300, 'sine');
        }

        function playFinalBeep() {
            // Play 3 beeps for final warning
            playBeep(800, 200, 'square');
            setTimeout(() => playBeep(800, 200, 'square'), 300);
            setTimeout(() => playBeep(800, 200, 'square'), 600);
        }

        function playEndSound() {
            // Long beep for end
            playBeep(1000, 1000, 'sawtooth');
        }

        function openTimerModal() {
            document.getElementById('timerModal').classList.add('active');
            updateTimerFromSettings();
        }

        function closeTimerModal() {
            document.getElementById('timerModal').classList.remove('active');
            pauseTimer();
        }

        function updateCandidateName() {
            const select = document.getElementById('timerCandidate');
            const name = select.value || 'Timer';
            document.getElementById('timerCandidateName').textContent = name;
        }

        function updateTimerFromSettings() {
            const minutes = parseInt(document.getElementById('timerMinutes').value) || 0;
            const seconds = parseInt(document.getElementById('timerSeconds').value) || 0;
            remainingSeconds = minutes * 60 + seconds;
            updateTimerDisplay();

            // Update warning sounds
            const soundSetting = document.getElementById('soundWarning').value;
            if (soundSetting === 'none') {
                warningSounds = [];
            } else {
                warningSounds = soundSetting.split(',').map(s => parseInt(s.trim()));
            }
            playedWarnings.clear();
        }

        function updateTimerDisplay() {
            const minutes = Math.floor(remainingSeconds / 60);
            const seconds = remainingSeconds % 60;
            const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            const timerDisplay = document.getElementById('timerDisplay');
            timerDisplay.textContent = display;

            // Update colors based on remaining time
            timerDisplay.classList.remove('warning', 'danger');
            if (remainingSeconds <= 10) {
                timerDisplay.classList.add('danger');
            } else if (remainingSeconds <= 30) {
                timerDisplay.classList.add('warning');
            }
        }

        function startTimer() {
            if (isRunning) return;
            if (remainingSeconds <= 0) {
                updateTimerFromSettings();
            }

            isRunning = true;
            updateStartPauseButton();
            timerInterval = setInterval(() => {
                remainingSeconds--;

                // Check for warning sounds
                if (warningSounds.includes(remainingSeconds) && !playedWarnings.has(remainingSeconds)) {
                    playedWarnings.add(remainingSeconds);
                    if (remainingSeconds <= 5) {
                        playFinalBeep();
                    } else {
                        playWarningBeep();
                    }
                }

                // Timer ended
                if (remainingSeconds <= 0) {
                    remainingSeconds = 0;
                    pauseTimer();
                    playEndSound();
                }

                updateTimerDisplay();
            }, 1000);
        }

        function pauseTimer() {
            isRunning = false;
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
            updateStartPauseButton();
        }

        function toggleTimer() {
            if (isRunning) {
                pauseTimer();
            } else {
                startTimer();
            }
        }

        function updateStartPauseButton() {
            const btn = document.getElementById('startPauseBtn');
            if (!btn) return;
            if (isRunning) {
                btn.textContent = '⏸ Pause';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-warning');
            } else {
                btn.textContent = '▶ Mulai';
                btn.classList.remove('btn-warning');
                btn.classList.add('btn-success');
            }
        }

        function resetTimer() {
            pauseTimer();
            playedWarnings.clear();
            updateTimerFromSettings();
            updateStartPauseButton();
        }

        function toggleTimerSettings() {
            const settings = document.querySelector('.timer-settings');
            if (settings.style.display === 'none' || settings.style.display === '') {
                settings.style.display = 'flex';
            } else {
                settings.style.display = 'none';
            }
        }

        // Handle keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (!document.getElementById('timerModal').classList.contains('active')) return;

            if (e.code === 'Space') {
                e.preventDefault();
                if (isRunning) {
                    pauseTimer();
                } else {
                    startTimer();
                }
            } else if (e.code === 'KeyR') {
                resetTimer();
            } else if (e.code === 'Escape') {
                closeTimerModal();
            }
        });
    </script>
</body>

</html>
