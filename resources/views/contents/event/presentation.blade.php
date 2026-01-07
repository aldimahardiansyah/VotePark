<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $event->name }} - Presentation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .qr-container {
            background: white;
            padding: 20px;
            border-radius: 15px;
            display: inline-block;
        }
        .event-info {
            text-align: center;
            padding: 40px;
        }
        .event-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .event-date {
            font-size: 1.5rem;
            opacity: 0.8;
        }
        .stats-card {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            margin: 10px;
        }
        .stats-number {
            font-size: 3rem;
            font-weight: bold;
            color: #14b8a6;
        }
        .register-link {
            margin-top: 20px;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <button class="btn btn-light fullscreen-btn" onclick="toggleFullscreen()">
        <span id="fullscreen-icon">⛶ Fullscreen</span>
    </button>

    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="event-info">
                    <h1 class="event-title">{{ $event->name }}</h1>
                    <p class="event-date">{{ \Carbon\Carbon::parse($event->date)->format('l, d F Y') }}</p>
                </div>

                <div class="row justify-content-center mb-5">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number">{{ $event->approvedUnits->count() }}</div>
                            <div>Registered Units</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number">{{ number_format($event->approvedUnits->sum('npp'), 2) }}%</div>
                            <div>Total NPP</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number">{{ $event->approvedUnits->pluck('user')->unique()->count() }}</div>
                            <div>Unique Owners</div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <h3 class="mb-4">Scan to Register</h3>
                    <div class="qr-container">
                        <div id="qrcode"></div>
                    </div>
                    <div class="register-link mt-4">
                        <p>Or visit: <a href="{{ route('event.register', $event->id) }}" class="text-info" target="_blank">{{ route('event.register', $event->id) }}</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <script>
        // Generate QR Code
        var qr = qrcode(0, 'M');
        qr.addData('{{ route('event.register', $event->id) }}');
        qr.make();
        document.getElementById('qrcode').innerHTML = qr.createImgTag(8, 8);

        // Fullscreen toggle
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                document.getElementById('fullscreen-icon').textContent = '✕ Exit Fullscreen';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                    document.getElementById('fullscreen-icon').textContent = '⛶ Fullscreen';
                }
            }
        }

        // Auto-refresh stats every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
