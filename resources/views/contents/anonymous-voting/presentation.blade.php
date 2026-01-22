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
    </style>
</head>

<body>
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
    </script>
</body>

</html>
