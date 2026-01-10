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
            max-width: 700px;
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

        .document-section {
            display: none;
            background: #fff3cd;
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
        }

        .document-section.show {
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
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('event.register.submit', $event->id) }}" method="POST" id="registerForm" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="unit_id" class="form-label fw-bold">Pilih Unit <span class="text-danger">*</span></label>
                            <select class="form-select" id="unit_id" name="unit_id" required>
                                <option value="">-- Pilih Unit --</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" data-info="{{ json_encode([
                                        'name' => $unit->user->name ?? '',
                                        'npp' => $unit->npp ?? '-',
                                        'email' => $unit->user->email ?? '',
                                        'code' => $unit->code,
                                    ]) }}">
                                        {{ $unit->code }} - {{ $unit->user->name ?? 'No Owner' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="unit-info" id="unitInfo">
                            <h6 class="fw-bold mb-3">Informasi Unit</h6>
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" width="120">Kode Unit</td>
                                    <td><strong id="infoCode">-</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Nama Pemilik</td>
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

                        <div class="mb-3 mt-3" id="attendeeSection" style="display: none;">
                            <label for="attendee_name" class="form-label fw-bold">Nama Peserta <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="attendee_name" name="attendee_name" placeholder="Masukkan nama peserta" required>
                        </div>

                        <div class="mb-3" id="emailField" style="display: none;">
                            <label for="email" class="form-label fw-bold">Email Anda</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan alamat email Anda (opsional)">
                        </div>

                        <div class="mb-3" id="phoneField" style="display: none;">
                            <label for="phone_number" class="form-label fw-bold">No. Telepon</label>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number" placeholder="Masukkan nomor telepon (opsional)">
                        </div>

                        <div class="mb-3" id="attendanceTypeSection" style="display: none;">
                            <label class="form-label fw-bold">Tipe Kehadiran <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attendance_type" id="attendance_owner" value="owner">
                                <label class="form-check-label" for="attendance_owner">
                                    Saya adalah pemilik unit
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attendance_type" id="attendance_representative" value="representative">
                                <label class="form-check-label" for="attendance_representative">
                                    Saya adalah perwakilan pemilik unit
                                </label>
                            </div>
                        </div>

                        <!-- Owner Documents Section -->
                        <div class="document-section" id="ownerDocuments">
                            <h6 class="fw-bold mb-3">Dokumen yang Diperlukan untuk Pemilik Unit</h6>
                            <p class="text-muted small">Silakan unggah dokumen berikut sesuai kebutuhan:</p>

                            <div class="mb-3">
                                <label for="civil_documents" class="form-label">Dokumen Kependudukan (KTP & KK) - dapat upload lebih dari satu file</label>
                                <input type="file" class="form-control" id="civil_documents" name="civil_documents[]" accept="image/*,.pdf" multiple>
                                <small class="form-text text-muted">Anda dapat memilih beberapa file sekaligus.</small>
                            </div>

                            <div class="mb-3">
                                <label for="ppjb_document" class="form-label">File PPJB (Gambar/PDF, max 7MB)</label>
                                <input type="file" class="form-control" id="ppjb_document" name="ppjb_document" accept="image/*,.pdf">
                            </div>

                            <div class="mb-3">
                                <label for="bukti_lunas_document" class="form-label">File Bukti Lunas (Gambar/PDF, max 7MB)</label>
                                <input type="file" class="form-control" id="bukti_lunas_document" name="bukti_lunas_document" accept="image/*,.pdf">
                            </div>

                            <div class="mb-3">
                                <label for="sjb_shm_document" class="form-label">File AJB/SHM Sarusun (Gambar/PDF, max 7MB)</label>
                                <input type="file" class="form-control" id="sjb_shm_document" name="sjb_shm_document" accept="image/*,.pdf">
                            </div>
                        </div>

                        <!-- Representative Documents Section -->
                        <div class="document-section" id="representativeDocuments">
                            <h6 class="fw-bold mb-3">Dokumen yang Diperlukan untuk Perwakilan</h6>
                            <p class="text-muted small">Silakan unggah dokumen berikut sesuai kebutuhan:</p>

                            <div class="mb-3">
                                <label for="identity_documents" class="form-label">KTP pemberi & penerima kuasa (dapat upload lebih dari satu file)</label>
                                <input type="file" class="form-control" id="identity_documents" name="identity_documents[]" accept="image/*,.pdf" multiple>
                                <small class="form-text text-muted">Anda dapat memilih beberapa file sekaligus.</small>
                            </div>

                            <div class="mb-3">
                                <label for="power_of_attorney" class="form-label">Surat Kuasa bermaterai</label>
                                <input type="file" class="form-control" id="power_of_attorney" name="power_of_attorney" accept="image/*,.pdf">
                            </div>

                            <div class="mb-3">
                                <label for="family_card" class="form-label">Kartu Keluarga (KK) - jika saudara/anak/orang tua</label>
                                <input type="file" class="form-control" id="family_card" name="family_card" accept="image/*,.pdf">
                            </div>

                            <div class="mb-3">
                                <label for="company_documents" class="form-label">Dokumen Perusahaan (Akta Pendirian & Akta Perubahan) - jika perusahaan</label>
                                <input type="file" class="form-control" id="company_documents" name="company_documents" accept="image/*,.pdf">
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                Daftar untuk Acara
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="text-center mt-3">
            <small class="text-muted">
                @if ($event->requires_approval)
                    Catatan: Pendaftaran Anda akan memerlukan persetujuan dari penyelenggara.
                @endif
            </small>
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
                    $('#infoNpp').text(Number(info.npp || 0).toFixed(2));
                    $('#infoEmail').text(info.email || '-');
                    $('#unitInfo').addClass('show');
                    $('#attendeeSection').show();
                    $('#emailField').show();
                    $('#phoneField').show();
                    $('#attendanceTypeSection').show();
                    $('#submitBtn').prop('disabled', false);
                } else {
                    $('#unitInfo').removeClass('show');
                    $('#emailField').hide();
                    $('#phoneField').hide();
                    $('#attendeeSection').hide();
                    $('#attendanceTypeSection').hide();
                    $('#ownerDocuments').removeClass('show');
                    $('#representativeDocuments').removeClass('show');
                    $('#submitBtn').prop('disabled', true);
                }
            });

            // Handle attendance type change
            $('input[name="attendance_type"]').on('change', function() {
                var type = $(this).val();
                if (type === 'owner') {
                    $('#ownerDocuments').addClass('show');
                    $('#representativeDocuments').removeClass('show');
                } else if (type === 'representative') {
                    $('#ownerDocuments').removeClass('show');
                    $('#representativeDocuments').addClass('show');
                }
            });

            // File size validation (7MB max)
            $('input[type="file"]').on('change', function() {
                var maxSize = 7 * 1024 * 1024; // 7MB
                if (this.files[0] && this.files[0].size > maxSize) {
                    alert('File size must not exceed 7MB');
                    this.value = '';
                }
            });

            // Clear all files if attendance type is changed
            $('input[name="attendance_type"]').on('change', function() {
                var type = $(this).val();
                if (type === 'owner') {
                    // Clear representative files
                    $('#power_of_attorney').val('');
                    $('#identity_documents').val('');
                    $('#family_card').val('');
                    $('#company_documents').val('');
                } else if (type === 'representative') {
                    // Clear owner files
                    $('#ppjb_document').val('');
                    $('#bukti_lunas_document').val('');
                    $('#sjb_shm_document').val('');
                    $('#civil_documents').val('');
                }
            });
        });
    </script>
</body>

</html>
