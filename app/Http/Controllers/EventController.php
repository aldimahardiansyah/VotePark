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
            'requires_photo' => 'nullable|boolean',
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
            'requires_photo' => $request->has('requires_photo'),
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
            'requires_photo' => 'nullable|boolean',
        ];

        if ($user->isHoldingAdmin()) {
            $rules['site_id'] = 'nullable|exists:sites,id';
        }

        $request->validate($rules);

        $updateData = [
            'name' => $request->name,
            'date' => $request->date,
            'requires_approval' => $request->has('requires_approval'),
            'requires_photo' => $request->has('requires_photo'),
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

    public function editParticipant(Event $event, Unit $unit)
    {
        // Check if unit is registered for this event
        $participant = $event->units()->where('unit_id', $unit->id)->first();
        if (!$participant) {
            return redirect()->route('event.show', $event->id)->with('error', 'Participant not found.');
        }

        return view('contents.event.edit-participant', compact('event', 'unit', 'participant'));
    }

    public function updateParticipant(Request $request, Event $event, Unit $unit)
    {
        // Check if unit is registered for this event
        if (!$event->units()->where('unit_id', $unit->id)->exists()) {
            return redirect()->route('event.show', $event->id)->with('error', 'Participant not found.');
        }

        $validationRules = [
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string|max:20',
            'attendee_name' => 'required|string|max:255',
            'attendance_type' => 'required|in:owner,representative',
            'ppjb_document.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'bukti_lunas_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'sjb_shm_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'civil_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'power_of_attorney' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'identity_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'family_card' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'company_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
        ];

        $request->validate($validationRules);

        $userEmail = $unit->user->email ?? '';
        $defaultDomain = '@' . config('app.default_email_domain', 'proapps.id');

        // Check if email ends with default domain and custom email is provided
        $registeredEmail = $userEmail;
        if (str_ends_with($userEmail, $defaultDomain) && $request->filled('email')) {
            $registeredEmail = $request->email;
        } elseif (!str_ends_with($userEmail, $defaultDomain)) {
            $registeredEmail = $userEmail;
        }

        // Get current participant data
        $currentParticipant = $event->units()->where('unit_id', $unit->id)->first();

        // Prepare update data
        $updateData = [
            'registered_email' => $registeredEmail,
            'phone_number' => $request->phone_number,
            'attendee_name' => $request->attendee_name,
            'attendance_type' => $request->attendance_type,
        ];

        // Handle file uploads - keep existing files if not uploading new ones
        $singleFileFields = ['bukti_lunas_document', 'sjb_shm_document', 'power_of_attorney', 'family_card'];

        foreach ($singleFileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . '_' . $field . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('event_documents/' . $event->id, $filename, 'public');
                $updateData[$field] = $path;
            }
        }

        // Handle multiple PPJB/Bukti Kepemilikan documents
        if ($request->hasFile('ppjb_document')) {
            $ppjbPaths = [];
            foreach ($request->file('ppjb_document') as $index => $file) {
                $filename = time() . '_ppjb_' . $index . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('event_documents/' . $event->id, $filename, 'public');
                $ppjbPaths[] = $path;
            }
            $updateData['ppjb_document'] = json_encode($ppjbPaths);
        }

        // Handle multiple civil documents (KTP for owner)
        if ($request->hasFile('civil_documents')) {
            $civilPaths = [];
            foreach ($request->file('civil_documents') as $index => $file) {
                $filename = time() . '_civil_' . $index . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('event_documents/' . $event->id, $filename, 'public');
                $civilPaths[] = $path;
            }
            $updateData['civil_documents'] = json_encode($civilPaths);
        }

        // Handle multiple identity documents
        if ($request->hasFile('identity_documents')) {
            $identityPaths = [];
            foreach ($request->file('identity_documents') as $index => $file) {
                $filename = time() . '_identity_' . $index . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('event_documents/' . $event->id, $filename, 'public');
                $identityPaths[] = $path;
            }
            $updateData['identity_documents'] = json_encode($identityPaths);
        }

        // Handle multiple company documents
        if ($request->hasFile('company_documents')) {
            $companyPaths = [];
            foreach ($request->file('company_documents') as $index => $file) {
                $filename = time() . '_company_' . $index . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('event_documents/' . $event->id, $filename, 'public');
                $companyPaths[] = $path;
            }
            $updateData['company_documents'] = json_encode($companyPaths);
        }

        // Update participant
        $event->units()->updateExistingPivot($unit->id, $updateData);

        return redirect()->route('event.show', $event->id)->with('success', 'Participant updated successfully.');
    }

    public function exportParticipants(Event $event)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = ['No', 'Owner Name', 'Attendee Name', 'Attendance Type', 'NPP', 'Unit Code', 'Email', 'Phone Number', 'Status', 'Documents'];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }

        // Get participants
        $participants = $event->requires_approval
            ? $event->units()->wherePivotIn('status', ['approved', 'pending'])->get()
            : $event->approvedUnits;

        // Add data
        $row = 2;
        foreach ($participants as $index => $unit) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $unit->user->name ?? '-');
            $sheet->setCellValue('C' . $row, $unit->pivot->attendee_name ?? '-');
            $sheet->setCellValue('D' . $row, ucfirst($unit->pivot->attendance_type ?? '-'));
            $sheet->setCellValue('E' . $row, number_format($unit->npp ?? 0, 2, '.', ','));
            $sheet->setCellValue('F' . $row, $unit->code);
            $sheet->setCellValue('G' . $row, $unit->pivot->registered_email ?? $unit->user->email);
            $sheet->setCellValue('H' . $row, $unit->pivot->phone_number ?? '-');
            $sheet->setCellValue('I' . $row, ucfirst($unit->pivot->status ?? 'approved'));

            // Compile document links
            $documents = [];
            $baseUrl = url('/');

            if ($unit->pivot->ppjb_document) {
                $ppjbDocs = json_decode($unit->pivot->ppjb_document);
                if (!is_array($ppjbDocs)) {
                    $ppjbDocs = [$unit->pivot->ppjb_document];
                }
                foreach ($ppjbDocs as $idx => $doc) {
                    $documents[] = 'Bukti Kepemilikan ' . ($idx + 1) . ': ' . $baseUrl . '/storage/' . $doc;
                }
            }
            if ($unit->pivot->bukti_lunas_document) {
                $documents[] = 'Bukti Lunas: ' . $baseUrl . '/storage/' . $unit->pivot->bukti_lunas_document;
            }
            if ($unit->pivot->sjb_shm_document) {
                $documents[] = 'AJB/SHM: ' . $baseUrl . '/storage/' . $unit->pivot->sjb_shm_document;
            }
            if ($unit->pivot->civil_documents) {
                $civilDocs = json_decode($unit->pivot->civil_documents);
                foreach ($civilDocs as $idx => $doc) {
                    $documents[] = 'KTP ' . ($idx + 1) . ': ' . $baseUrl . '/storage/' . $doc;
                }
            }
            if ($unit->pivot->identity_documents) {
                $identityDocs = json_decode($unit->pivot->identity_documents);
                foreach ($identityDocs as $idx => $doc) {
                    $documents[] = 'KTP/Identitas ' . ($idx + 1) . ': ' . $baseUrl . '/storage/' . $doc;
                }
            }
            if ($unit->pivot->family_card) {
                $documents[] = 'KK/akte nikah: ' . $baseUrl . '/storage/' . $unit->pivot->family_card;
            }
            if ($unit->pivot->power_of_attorney) {
                $documents[] = 'Surat Kuasa: ' . $baseUrl . '/storage/' . $unit->pivot->power_of_attorney;
            }
            if ($unit->pivot->company_documents) {
                $companyDocs = json_decode($unit->pivot->company_documents);
                if (!is_array($companyDocs)) {
                    $companyDocs = [$unit->pivot->company_documents];
                }
                foreach ($companyDocs as $idx => $doc) {
                    $documents[] = 'Dokumen Perusahaan ' . ($idx + 1) . ': ' . $baseUrl . '/storage/' . $doc;
                }
            }
            if ($unit->pivot->participant_photo) {
                $documents[] = 'Foto Peserta: ' . $baseUrl . '/storage/' . $unit->pivot->participant_photo;
            }

            $sheet->setCellValue('J' . $row, implode("\n", $documents));
            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        $fileName = 'participants_' . $event->name . '_' . now()->format('Y-m-d') . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
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
        $validationRules = [
            'unit_id' => 'required|exists:units,id',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string|max:20',
            'attendee_name' => 'required|string|max:255',
            'attendance_type' => 'required|in:owner,representative',
            'ownership_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'ppjb_document.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'bukti_lunas_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'sjb_shm_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'civil_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'power_of_attorney' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'identity_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'family_card' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
            'company_documents.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:7168',
        ];

        // Add photo validation if event requires photo
        if ($event->requires_photo) {
            $validationRules['participant_photo'] = 'required|string';
        }

        $request->validate($validationRules);

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
        $singleFileFields = ['ownership_proof', 'bukti_lunas_document', 'sjb_shm_document', 'power_of_attorney', 'family_card'];

        foreach ($singleFileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . '_' . $field . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('event_documents/' . $event->id, $filename, 'public');
                $uploadedFiles[$field] = $path;
            }
        }

        // Handle multiple PPJB/Bukti Kepemilikan documents
        if ($request->hasFile('ppjb_document')) {
            $ppjbPaths = [];
            foreach ($request->file('ppjb_document') as $index => $file) {
                // Without original name to avoid issues
                $filename = time() . '_ppjb_' . $index . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('event_documents/' . $event->id, $filename, 'public');
                $ppjbPaths[] = $path;
            }
            $uploadedFiles['ppjb_document'] = json_encode($ppjbPaths);
        }

        // Handle multiple civil documents (KTP for owner)
        if ($request->hasFile('civil_documents')) {
            $civilPaths = [];
            foreach ($request->file('civil_documents') as $index => $file) {
                $filename = time() . '_civil_' . $index . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('event_documents/' . $event->id, $filename, 'public');
                $civilPaths[] = $path;
            }
            $uploadedFiles['civil_documents'] = json_encode($civilPaths);
        }

        // Handle multiple identity documents
        if ($request->hasFile('identity_documents')) {
            $identityPaths = [];
            foreach ($request->file('identity_documents') as $index => $file) {
                $filename = time() . '_identity_' . $index . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('event_documents/' . $event->id, $filename, 'public');
                $identityPaths[] = $path;
            }
            $uploadedFiles['identity_documents'] = json_encode($identityPaths);
        }

        // Handle multiple company documents
        if ($request->hasFile('company_documents')) {
            $companyPaths = [];
            foreach ($request->file('company_documents') as $index => $file) {
                $filename = time() . '_company_' . $index . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('event_documents/' . $event->id, $filename, 'public');
                $companyPaths[] = $path;
            }
            $uploadedFiles['company_documents'] = json_encode($companyPaths);
        }

        // Handle photo capture (base64 data)
        if ($event->requires_photo && $request->filled('participant_photo')) {
            $photoData = $request->participant_photo;
            // Remove the data URL prefix (data:image/jpeg;base64,)
            $photoData = preg_replace('/^data:image\/\w+;base64,/', '', $photoData);
            $photoData = base64_decode($photoData);

            $filename = time() . '_participant_photo_' . $unit->id . '.jpg';
            $path = 'event_documents/' . $event->id . '/' . $filename;
            \Storage::disk('public')->put($path, $photoData);
            $uploadedFiles['participant_photo'] = $path;
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
            'participant_photo' => $uploadedFiles['participant_photo'] ?? null,
        ]);

        $message = $event->requires_approval
            ? 'Registration submitted. Waiting for approval.'
            : 'Successfully registered for the event.';

        return redirect()->route('event.register', $event->id)->with('success', $message);
    }
}
