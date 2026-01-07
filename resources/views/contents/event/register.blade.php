<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - {{ $event->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .register-card {
            max-width: 600px;
            margin: 0 auto;
        }
        .event-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            text-align: center;
            padding: 30px;
            border-radius: 15px 15px 0 0;
        }
        .unit-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            display: none;
        }
        .unit-info.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-card">
            <div class="event-header">
                <h2>{{ $event->name }}</h2>
                <p class="mb-0">{{ \Carbon\Carbon::parse($event->date)->format('l, d F Y') }}</p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('event.register.submit', $event->id) }}" method="POST" id="registerForm">
                        @csrf

                        <div class="mb-3">
                            <label for="unit_id" class="form-label fw-bold">Select Unit</label>
                            <select class="form-select" id="unit_id" name="unit_id" required>
                                <option value="">-- Select Unit --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" data-info="{{ json_encode([
                                        'name' => $unit->user->name ?? '',
                                        'npp' => $unit->npp ?? '-',
                                        'email' => $unit->user->email ?? '',
                                        'code' => $unit->code
                                    ]) }}">
                                        {{ $unit->code }} - {{ $unit->user->name ?? 'No Owner' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="unit-info" id="unitInfo">
                            <h6 class="fw-bold mb-3">Unit Information</h6>
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" width="120">Unit Code</td>
                                    <td><strong id="infoCode">-</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Owner Name</td>
                                    <td><strong id="infoName">-</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">NPP</td>
                                    <td><strong id="infoNpp">-</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email</td>
                                    <td><strong id="infoEmail">-</strong></td>
                                </tr>
                            </table>
                        </div>

                        <div class="mb-3 mt-3" id="emailField" style="display: none;">
                            <label for="email" class="form-label fw-bold">Your Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address">
                            <div class="form-text">This unit uses a default @{{ config('app.default_email_domain', 'proapps.id') }} email. Please provide your actual email address.</div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                Register for Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="text-center mt-3">
                <small class="text-muted">
                    @if($event->requires_approval)
                        Note: Your registration will require approval from the organizer.
                    @endif
                </small>
            </div>
        </div>
    </div>

    @php
        $defaultDomain = '@' . config('app.default_email_domain', 'proapps.id');
    @endphp
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            var defaultDomain = '{{ $defaultDomain }}';

            $('#unit_id').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Select Unit --',
                allowClear: true
            });

            $('#unit_id').on('change', function() {
                var selected = $(this).find(':selected');
                var info = selected.data('info');

                if (info) {
                    $('#infoCode').text(info.code || '-');
                    $('#infoName').text(info.name || '-');
                    $('#infoNpp').text(info.npp || '-');
                    $('#infoEmail').text(info.email || '-');
                    $('#unitInfo').addClass('show');
                    $('#submitBtn').prop('disabled', false);

                    // Check if email ends with default domain
                    if (info.email && info.email.endsWith(defaultDomain)) {
                        $('#emailField').show();
                        $('#email').prop('required', true);
                    } else {
                        $('#emailField').hide();
                        $('#email').prop('required', false);
                        $('#email').val('');
                    }
                } else {
                    $('#unitInfo').removeClass('show');
                    $('#emailField').hide();
                    $('#submitBtn').prop('disabled', true);
                }
            });
        });
    </script>
</body>
</html>
