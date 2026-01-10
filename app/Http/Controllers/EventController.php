<?php

namespace App\Http\Controllers;

use App\Imports\ParticipantImport;
use App\Models\Event;
use App\Models\Site;
use App\Models\Unit;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        $query = Event::query();
        if ($user->isSiteAdmin() && $user->site_id) {
            $query->where('site_id', $user->site_id);
        }

        $events = $query->get();
        return view('contents.event.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $sites = $user->isHoldingAdmin() ? Site::all() : collect();
        $userSiteId = $user->site_id;

        return view('contents.event.create', compact('sites', 'userSiteId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'requires_approval' => 'nullable|boolean',
        ];

        if ($user->isHoldingAdmin()) {
            $rules['site_id'] = 'nullable|exists:sites,id';
        }

        $request->validate($rules);

        $siteId = $user->isHoldingAdmin() ? $request->input('site_id') : $user->site_id;

        Event::create([
            'name' => $request->name,
            'date' => $request->date,
            'requires_approval' => $request->has('requires_approval'),
            'site_id' => $siteId,
        ]);

        return redirect()->route('event.index')->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        // Show event details with approved units only for counting
        return view('contents.event.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        $user = auth()->user();
        $sites = $user->isHoldingAdmin() ? Site::all() : collect();
        $userSiteId = $user->site_id;

        return view('contents.event.edit', compact('event', 'sites', 'userSiteId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $user = auth()->user();

        $rules = [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'requires_approval' => 'nullable|boolean',
        ];

        if ($user->isHoldingAdmin()) {
            $rules['site_id'] = 'nullable|exists:sites,id';
        }

        $request->validate($rules);

        $updateData = [
            'name' => $request->name,
            'date' => $request->date,
            'requires_approval' => $request->has('requires_approval'),
        ];

        if ($user->isHoldingAdmin()) {
            $updateData['site_id'] = $request->input('site_id');
        }

        $event->update($updateData);

        return redirect()->route('event.index')->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('event.index')->with('success', 'Event deleted successfully.');
    }

    public function importParticipant(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
            'event_id' => 'required|exists:events,id',
        ]);

        $event = Event::find($request->event_id);

        Excel::import(new ParticipantImport($event), $request->file('file'));

        return redirect()->route('event.show', $event->id)->with('success', 'Participants imported successfully.');
    }

    public function downloadParticipantTemplate(Event $event)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'email');

        // Add example row
        $sheet->setCellValue('A2', 'john@example.com');

        $writer = new Xlsx($spreadsheet);

        $fileName = 'participant_import_template.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function addParticipant(Request $request)
    {
        $request->validate([
            'event_id' => 'exists:events,id',
            'unit_id.*' => 'required|exists:units,id',
        ]);

        $event = Event::find($request->event_id);
        $status = $event->requires_approval ? 'pending' : 'approved';

        foreach ($request->unit_id as $unitId) {
            $unit = Unit::find($unitId);
            if (!$event->units()->where('unit_id', $unitId)->exists()) {
                $event->units()->attach($unitId, [
                    'unit_code' => $unit->code,
                    'status' => $status,
                ]);
            }
        }

        return redirect()->route('event.show', $event->id)->with('success', 'Participant added successfully.');
    }

    public function removeParticipant(Request $request)
    {
        $request->validate([
            'event_id' => 'exists:events,id',
            'unit_id' => 'required|exists:units,id',
        ]);

        $event = Event::find($request->event_id);
        $event->units()->detach($request->unit_id);

        return redirect()->route('event.show', $event->id)->with('success', 'Participant removed successfully.');
    }

    public function approveParticipant(Request $request)
    {
        $request->validate([
            'event_id' => 'exists:events,id',
            'unit_id' => 'required|exists:units,id',
        ]);

        $event = Event::find($request->event_id);
        $event->units()->updateExistingPivot($request->unit_id, ['status' => 'approved']);

        return redirect()->route('event.show', $event->id)->with('success', 'Participant approved successfully.');
    }

    public function rejectParticipant(Request $request)
    {
        $request->validate([
            'event_id' => 'exists:events,id',
            'unit_id' => 'required|exists:units,id',
        ]);

        $event = Event::find($request->event_id);
        $event->units()->updateExistingPivot($request->unit_id, ['status' => 'rejected']);

        return redirect()->route('event.show', $event->id)->with('success', 'Participant rejected.');
    }

    public function rejectedParticipants(Event $event)
    {
        return view('contents.event.rejected-participants', compact('event'));
    }

    public function presentation(Event $event)
    {
        $units = Unit::with('user')->get();
        return view('contents.event.presentation', compact('event', 'units'));
    }

    public function registerForm(Event $event)
    {
        $units = Unit::with('user')->orderBy('code')->get();
        return view('contents.event.register', compact('event', 'units'));
    }

    public function registerParticipant(Request $request, Event $event)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string|max:20',
            'attendee_name' => 'required|string|max:255',
            'attendance_type' => 'required|in:owner,representative',
            'ownership_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'ppjb_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'bukti_lunas_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'sjb_shm_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'civil_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'power_of_attorney' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'identity_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'family_card' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'company_documents' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
        ]);

        $unit = Unit::find($request->unit_id);
        $userEmail = $unit->user->email ?? '';
        $defaultDomain = '@' . config('app.default_email_domain', 'proapps.id');

        // Check if email ends with default domain and custom email is provided
        $registeredEmail = $userEmail;
        if (str_ends_with($userEmail, $defaultDomain) && $request->filled('email')) {
            $registeredEmail = $request->email;
        } elseif (!str_ends_with($userEmail, $defaultDomain)) {
            $registeredEmail = $userEmail;
        }

        // Check if already registered
        if ($event->units()->where('unit_id', $unit->id)->exists()) {
            return redirect()->route('event.register', $event->id)
                ->with('error', 'Unit already registered for this event.');
        }

        $status = $event->requires_approval ? 'pending' : 'approved';

        // Handle file uploads
        $uploadedFiles = [];
        $singleFileFields = ['ownership_proof', 'ppjb_document', 'bukti_lunas_document', 'sjb_shm_document', 'power_of_attorney', 'family_card', 'company_documents'];

        foreach ($singleFileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . '_' . $field . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('event_documents/' . $event->id, $filename, 'public');
                $uploadedFiles[$field] = $path;
            }
        }

        // Handle multiple civil documents (KTP & KK for owner)
        if ($request->hasFile('civil_documents')) {
            $civilPaths = [];
            foreach ($request->file('civil_documents') as $index => $file) {
                $filename = time() . '_civil_' . $index . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('event_documents/' . $event->id, $filename, 'public');
                $civilPaths[] = $path;
            }
            $uploadedFiles['civil_documents'] = json_encode($civilPaths);
        }

        // Handle multiple identity documents
        if ($request->hasFile('identity_documents')) {
            $identityPaths = [];
            foreach ($request->file('identity_documents') as $index => $file) {
                $filename = time() . '_identity_' . $index . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('event_documents/' . $event->id, $filename, 'public');
                $identityPaths[] = $path;
            }
            $uploadedFiles['identity_documents'] = json_encode($identityPaths);
        }

        $event->units()->attach($unit->id, [
            'unit_code' => $unit->code,
            'status' => $status,
            'registered_email' => $registeredEmail,
            'phone_number' => $request->phone_number,
            'attendee_name' => $request->attendee_name,
            'attendance_type' => $request->attendance_type,
            'ownership_proof' => $uploadedFiles['ownership_proof'] ?? null,
            'ppjb_document' => $uploadedFiles['ppjb_document'] ?? null,
            'bukti_lunas_document' => $uploadedFiles['bukti_lunas_document'] ?? null,
            'sjb_shm_document' => $uploadedFiles['sjb_shm_document'] ?? null,
            'civil_documents' => $uploadedFiles['civil_documents'] ?? null,
            'power_of_attorney' => $uploadedFiles['power_of_attorney'] ?? null,
            'identity_documents' => $uploadedFiles['identity_documents'] ?? null,
            'family_card' => $uploadedFiles['family_card'] ?? null,
            'company_documents' => $uploadedFiles['company_documents'] ?? null,
        ]);

        $message = $event->requires_approval
            ? 'Registration submitted. Waiting for approval.'
            : 'Successfully registered for the event.';

        return redirect()->route('event.register', $event->id)->with('success', $message);
    }
}
