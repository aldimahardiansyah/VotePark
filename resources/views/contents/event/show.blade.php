<x-layout.main>
    <x-alert.success-and-error />

    <!-- Toggle Buttons -->
    {{-- <div class="mb-3 text-end">
        <button type="button" class="btn btn-sm btn-outline-primary" id="toggleEventCards">
            <i class="bi bi-eye-slash"></i> Hide Event Cards
        </button>
    </div> --}}

    <div class="row">
        <div class="col-12 col-md-4" id="eventCardsColumn">
            <x-event.vote :event="$event" />
        </div>

        <div class="col-12 col-md-8" id="participantsColumn">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center pt-3">
                    <h5 class="text-center">Participants</h5>
                    <div>
                        @if ($event->rejectedUnits->count() > 0)
                            <a href="{{ route('event.rejected-participants', $event->id) }}" class="btn btn-sm btn-outline-danger me-2">
                                View Rejected ({{ $event->rejectedUnits->count() }})
                            </a>
                        @endif
                        {{-- <x-event.add-participant-modal :event="$event" /> --}}
                    </div>
                </div>
                <div class="card-body">
                    @if ($event->requires_approval && $event->pendingUnits->count() > 0)
                        <div class="alert alert-warning">
                            <strong>Pending Approvals:</strong> {{ $event->pendingUnits->count() }} participant(s) waiting for approval.
                        </div>
                    @endif

                    <table class="table table-striped" id="datatable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Owner</th>
                                <th>Attendee</th>
                                <th>Type</th>
                                <th>NPP</th>
                                <th>Unit</th>
                                @if ($event->requires_approval)
                                    <th>Status</th>
                                @endif
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $displayUnits = $event->requires_approval
                                    ? $event
                                        ->units()
                                        ->wherePivotIn('status', ['approved', 'pending'])
                                        ->get()
                                    : $event->approvedUnits;
                            @endphp
                            @foreach ($displayUnits as $unit)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $unit->user->name }}</td>
                                    <td>{{ $unit->pivot->attendee_name ?? '-' }}</td>
                                    <td>
                                        @if ($unit->pivot->attendance_type === 'owner')
                                            <span class="badge bg-primary">Owner</span>
                                        @elseif($unit->pivot->attendance_type === 'representative')
                                            <span class="badge bg-info">Representative</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $unit->npp ?? '-' }}</td>
                                    <td>{{ $unit->code }}</td>
                                    @if ($event->requires_approval)
                                        <td>
                                            @if ($unit->pivot->status === 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($unit->pivot->status === 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td>
                                        <div class="d-flex gap-1 flex-wrap">
                                            @if ($event->requires_approval && $unit->pivot->status === 'pending')
                                                <!-- View Documents Button -->
                                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#documentsModal{{ $unit->id }}">
                                                    View Docs
                                                </button>

                                                <!-- Documents Modal -->
                                                <div class="modal fade" id="documentsModal{{ $unit->id }}" tabindex="-1" aria-labelledby="documentsModalLabel{{ $unit->id }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="documentsModalLabel{{ $unit->id }}">Documents - {{ $unit->code }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <table class="table">
                                                                    <tr>
                                                                        <th>Owner Name</th>
                                                                        <td>{{ $unit->user->name }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Attendee Name</th>
                                                                        <td>{{ $unit->pivot->attendee_name ?? '-' }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Attendance Type</th>
                                                                        <td>{{ ucfirst($unit->pivot->attendance_type ?? '-') }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th>Email</th>
                                                                        <td>{{ $unit->pivot->registered_email ?? $unit->user->email }}</td>
                                                                    </tr>
                                                                </table>

                                                                <h6 class="mt-4">Uploaded Documents:</h6>
                                                                <ul class="list-group">
                                                                    @if ($unit->pivot->ppjb_document)
                                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                            PPJB
                                                                            <a href="{{ asset('storage/' . $unit->pivot->ppjb_document) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                                        </li>
                                                                    @endif
                                                                    @if ($unit->pivot->bukti_lunas_document)
                                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                            Bukti Lunas
                                                                            <a href="{{ asset('storage/' . $unit->pivot->bukti_lunas_document) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                                        </li>
                                                                    @endif
                                                                    @if ($unit->pivot->sjb_shm_document)
                                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                            SJB/SHM
                                                                            <a href="{{ asset('storage/' . $unit->pivot->sjb_shm_document) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                                        </li>
                                                                    @endif
                                                                    @if ($unit->pivot->civil_documents)
                                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                            KTP / Identitas
                                                                            @php
                                                                                $civilDocuments = json_decode($unit->pivot->civil_documents);
                                                                            @endphp
                                                                            <div>
                                                                                @foreach ($civilDocuments as $doc)
                                                                                    <a href="{{ asset('storage/' . $doc) }}" target="_blank" class="btn btn-sm btn-outline-primary me-1 mb-1">File {{ $loop->iteration }}</a>
                                                                                @endforeach
                                                                            </div>
                                                                        </li>
                                                                    @endif
                                                                    @if ($unit->pivot->identity_documents)
                                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                            KTP / Identitas
                                                                            @php
                                                                                $identityDocuments = json_decode($unit->pivot->identity_documents);
                                                                            @endphp
                                                                            <div>
                                                                                @foreach ($identityDocuments as $doc)
                                                                                    <a href="{{ asset('storage/' . $doc) }}" target="_blank" class="btn btn-sm btn-outline-primary me-1 mb-1">File {{ $loop->iteration }}</a>
                                                                                @endforeach
                                                                            </div>
                                                                        </li>
                                                                    @endif
                                                                    @if ($unit->pivot->family_card)
                                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                            Kartu Keluarga
                                                                            <a href="{{ asset('storage/' . $unit->pivot->family_card) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                                        </li>
                                                                    @endif
                                                                    @if ($unit->pivot->power_of_attorney)
                                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                            Surat Kuasa
                                                                            <a href="{{ asset('storage/' . $unit->pivot->power_of_attorney) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                                        </li>
                                                                    @endif
                                                                    @if ($unit->pivot->company_documents)
                                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                            Dokumen Perusahaan
                                                                            <a href="{{ asset('storage/' . $unit->pivot->company_documents) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                                                        </li>
                                                                    @endif
                                                                    @if (!$unit->pivot->ownership_proof && !$unit->pivot->power_of_attorney && !$unit->pivot->identity_documents && !$unit->pivot->family_card && !$unit->pivot->company_documents && !$unit->pivot->ppjb_document && !$unit->pivot->bukti_lunas_document && !$unit->pivot->sjb_shm_document)
                                                                        <li class="list-group-item text-muted">No documents uploaded</li>
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <form action="{{ route('event.reject-participant') }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                                                                    <input type="hidden" name="unit_id" value="{{ $unit->id }}">
                                                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to reject this participant?')">Reject</button>
                                                                </form>
                                                                <form action="{{ route('event.approve-participant') }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                                                                    <input type="hidden" name="unit_id" value="{{ $unit->id }}">
                                                                    <button type="submit" class="btn btn-success">Approve</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            <form action="{{ route('event.remove-participant') }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('delete')
                                                <input type="hidden" name="event_id" value="{{ $event->id }}">
                                                <input type="hidden" name="unit_id" value="{{ $unit->id }}">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Remove</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                let eventCardsVisible = true;

                $('#toggleEventCards').on('click', function() {
                    eventCardsVisible = !eventCardsVisible;

                    if (eventCardsVisible) {
                        // Show event cards
                        $('#eventCardsColumn').removeClass('d-none');
                        $('#participantsColumn').removeClass('col-md-12').addClass('col-md-8');
                        $(this).html('<i class="bi bi-eye-slash"></i> Hide Event Cards');
                    } else {
                        // Hide event cards
                        $('#eventCardsColumn').addClass('d-none');
                        $('#participantsColumn').removeClass('col-md-8').addClass('col-md-12');
                        $(this).html('<i class="bi bi-eye"></i> Show Event Cards');
                    }
                });
            });
        </script>
    @endpush
</x-layout.main>
