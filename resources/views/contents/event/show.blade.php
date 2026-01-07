<x-layout.main>
    <x-alert.success-and-error />

    <div class="row">
        <div class="col-12 col-md-4">
            <x-event.vote :event="$event" />
        </div>

        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center pt-3">
                    <h5 class="text-center">Participants</h5>
                    <div>
                        @if($event->rejectedUnits->count() > 0)
                            <a href="{{ route('event.rejected-participants', $event->id) }}" class="btn btn-sm btn-outline-danger me-2">
                                View Rejected ({{ $event->rejectedUnits->count() }})
                            </a>
                        @endif
                        <x-event.add-participant-modal :event="$event" />
                    </div>
                </div>
                <div class="card-body">
                    @if($event->requires_approval && $event->pendingUnits->count() > 0)
                        <div class="alert alert-warning">
                            <strong>Pending Approvals:</strong> {{ $event->pendingUnits->count() }} participant(s) waiting for approval.
                        </div>
                    @endif

                    <table class="table table-striped" id="datatable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>NPP</th>
                                <th>Unit</th>
                                <th>Email</th>
                                @if($event->requires_approval)
                                    <th>Status</th>
                                @endif
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $displayUnits = $event->requires_approval
                                    ? $event->units()->wherePivotIn('status', ['approved', 'pending'])->get()
                                    : $event->approvedUnits;
                            @endphp
                            @foreach ($displayUnits as $unit)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $unit->user->name }}</td>
                                    <td>{{ $unit->npp ?? '-' }}</td>
                                    <td>{{ $unit->code }}</td>
                                    <td>{{ $unit->pivot->registered_email ?? $unit->user->email }}</td>
                                    @if($event->requires_approval)
                                        <td>
                                            @if($unit->pivot->status === 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($unit->pivot->status === 'approved')
                                                <span class="badge bg-success">Approved</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td>
                                        <div class="d-flex gap-1">
                                            @if($event->requires_approval && $unit->pivot->status === 'pending')
                                                <form action="{{ route('event.approve-participant') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                                                    <input type="hidden" name="unit_id" value="{{ $unit->id }}">
                                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                </form>
                                                <form action="{{ route('event.reject-participant') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                                                    <input type="hidden" name="unit_id" value="{{ $unit->id }}">
                                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Are you sure?')">Reject</button>
                                                </form>
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
</x-layout.main>
