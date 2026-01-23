<x-layout.main>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Edit Participant - {{ $event->name }}</h5>
                    </div>
                    <div class="card-body">
                        <x-alert.success-and-error />

                        <form action="{{ route('event.update-participant', [$event->id, $unit->id]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label fw-bold">Unit Information</label>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td width="150">Unit Code</td>
                                        <td>: {{ $unit->code }}</td>
                                    </tr>
                                    <tr>
                                        <td>Owner Name</td>
                                        <td>: {{ $unit->user->name }}</td>
                                    </tr>
                                    <tr>
                                        <td>NPP</td>
                                        <td>: {{ number_format($unit->npp ?? 0, 2) }}</td>
                                    </tr>
                                </table>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label for="attendee_name" class="form-label fw-bold">Attendee Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('attendee_name') is-invalid @enderror" id="attendee_name" name="attendee_name" value="{{ old('attendee_name', $participant->pivot->attendee_name) }}" required>
                                @error('attendee_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $participant->pivot->registered_email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone_number" class="form-label fw-bold">Phone Number</label>
                                <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number" name="phone_number" value="{{ old('phone_number', $participant->pivot->phone_number) }}">
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Attendance Type <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="attendance_type" id="attendance_owner" value="owner" {{ old('attendance_type', $participant->pivot->attendance_type) == 'owner' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="attendance_owner">
                                        Owner (Pemilik)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="attendance_type" id="attendance_representative" value="representative" {{ old('attendance_type', $participant->pivot->attendance_type) == 'representative' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="attendance_representative">
                                        Representative (Perwakilan)
                                    </label>
                                </div>
                            </div>

                            <!-- Owner Documents Section -->
                            <div class="mb-3" id="ownerDocuments" style="display: {{ old('attendance_type', $participant->pivot->attendance_type) == 'owner' ? 'block' : 'none' }}">
                                <h6 class="fw-bold">Owner Documents</h6>
                                <p class="text-muted small">Upload new files to replace existing ones</p>

                                <div class="mb-3">
                                    <label for="civil_documents" class="form-label">KTP - dapat upload lebih dari satu file</label>
                                    <input type="file" class="form-control" id="civil_documents" name="civil_documents[]" accept="image/*,.pdf" multiple>
                                    @if($participant->pivot->civil_documents)
                                        <small class="text-muted">Current: {{ count(json_decode($participant->pivot->civil_documents)) }} file(s)</small>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label for="ppjb_document" class="form-label">Bukti Kepemilikan - dapat upload lebih dari satu file</label>
                                    <input type="file" class="form-control" id="ppjb_document" name="ppjb_document[]" accept="image/*,.pdf" multiple>
                                    @if($participant->pivot->ppjb_document)
                                        @php
                                            $ppjbDocs = json_decode($participant->pivot->ppjb_document);
                                            if (!is_array($ppjbDocs)) {
                                                $ppjbDocs = [$participant->pivot->ppjb_document];
                                            }
                                        @endphp
                                        <small class="text-muted">Current: {{ count($ppjbDocs) }} file(s)</small>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label for="bukti_lunas_document" class="form-label">Bukti Lunas</label>
                                    <input type="file" class="form-control" id="bukti_lunas_document" name="bukti_lunas_document" accept="image/*,.pdf">
                                    @if($participant->pivot->bukti_lunas_document)
                                        <small class="text-muted">Current file exists</small>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label for="sjb_shm_document" class="form-label">AJB/SHM Sarusun</label>
                                    <input type="file" class="form-control" id="sjb_shm_document" name="sjb_shm_document" accept="image/*,.pdf">
                                    @if($participant->pivot->sjb_shm_document)
                                        <small class="text-muted">Current file exists</small>
                                    @endif
                                </div>
                            </div>

                            <!-- Representative Documents Section -->
                            <div class="mb-3" id="representativeDocuments" style="display: {{ old('attendance_type', $participant->pivot->attendance_type) == 'representative' ? 'block' : 'none' }}">
                                <h6 class="fw-bold">Representative Documents</h6>
                                <p class="text-muted small">Upload new files to replace existing ones</p>

                                <div class="mb-3">
                                    <label for="identity_documents" class="form-label">KTP pemberi & penerima kuasa</label>
                                    <input type="file" class="form-control" id="identity_documents" name="identity_documents[]" accept="image/*,.pdf" multiple>
                                    @if($participant->pivot->identity_documents)
                                        <small class="text-muted">Current: {{ count(json_decode($participant->pivot->identity_documents)) }} file(s)</small>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label for="power_of_attorney" class="form-label">Surat Kuasa bermaterai</label>
                                    <input type="file" class="form-control" id="power_of_attorney" name="power_of_attorney" accept="image/*,.pdf">
                                    @if($participant->pivot->power_of_attorney)
                                        <small class="text-muted">Current file exists</small>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label for="family_card" class="form-label">KK/akte nikah - jika saudara/anak/orang tua</label>
                                    <input type="file" class="form-control" id="family_card" name="family_card" accept="image/*,.pdf">
                                    @if($participant->pivot->family_card)
                                        <small class="text-muted">Current file exists</small>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label for="company_documents" class="form-label">Dokumen Perusahaan - jika perusahaan</label>
                                    <input type="file" class="form-control" id="company_documents" name="company_documents[]" accept="image/*,.pdf" multiple>
                                    @if($participant->pivot->company_documents)
                                        @php
                                            $companyDocs = json_decode($participant->pivot->company_documents);
                                            if (!is_array($companyDocs)) {
                                                $companyDocs = [$participant->pivot->company_documents];
                                            }
                                        @endphp
                                        <small class="text-muted">Current: {{ count($companyDocs) }} file(s)</small>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Update Participant</button>
                                <a href="{{ route('event.show', $event->id) }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Handle attendance type change
                $('input[name="attendance_type"]').on('change', function() {
                    var type = $(this).val();
                    if (type === 'owner') {
                        $('#ownerDocuments').show();
                        $('#representativeDocuments').hide();
                    } else if (type === 'representative') {
                        $('#ownerDocuments').hide();
                        $('#representativeDocuments').show();
                    }
                });
            });
        </script>
    @endpush
</x-layout.main>
