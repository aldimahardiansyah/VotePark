<?php

namespace App\Http\Controllers;

use App\Imports\ParticipantImport;
use App\Models\Event;
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
        $events = Event::all();
        return view('contents.event.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('contents.event.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'requires_approval' => 'nullable|boolean',
        ]);

        Event::create([
            'name' => $request->name,
            'date' => $request->date,
            'requires_approval' => $request->has('requires_approval'),
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
        return view('contents.event.edit', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'requires_approval' => 'nullable|boolean',
        ]);

        $event->update([
            'name' => $request->name,
            'date' => $request->date,
            'requires_approval' => $request->has('requires_approval'),
        ]);

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
        $units = Unit::with('user')->get();
        return view('contents.event.register', compact('event', 'units'));
    }

    public function registerParticipant(Request $request, Event $event)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'email' => 'nullable|email',
        ]);

        $unit = Unit::find($request->unit_id);
        $userEmail = $unit->user->email ?? '';
        $defaultDomain = '@' . config('app.default_email_domain', 'proapps.id');

        // Check if email ends with default domain and custom email is required
        $registeredEmail = $userEmail;
        if (str_ends_with($userEmail, $defaultDomain)) {
            $request->validate([
                'email' => 'required|email',
            ]);
            $registeredEmail = $request->email;
        }

        // Check if already registered
        if ($event->units()->where('unit_id', $unit->id)->exists()) {
            return redirect()->route('event.register', $event->id)
                ->with('error', 'Unit already registered for this event.');
        }

        $status = $event->requires_approval ? 'pending' : 'approved';

        $event->units()->attach($unit->id, [
            'unit_code' => $unit->code,
            'status' => $status,
            'registered_email' => $registeredEmail,
        ]);

        $message = $event->requires_approval
            ? 'Registration submitted. Waiting for approval.'
            : 'Successfully registered for the event.';

        return redirect()->route('event.register', $event->id)->with('success', $message);
    }
}
