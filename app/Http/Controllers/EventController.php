<?php

namespace App\Http\Controllers;

use App\Imports\AttendanceEventImport;
use App\Models\Event;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
        ]);

        Event::create($request->all());

        return redirect()->route('event.index')->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        // Show event details
        return view('contents.event.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        //
    }

    public function importAttendance(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv',
        ]);

        // Handle file import logic here
        Excel::import(new AttendanceEventImport, $request->file('file'));

        return redirect()->route('event.index')->with('success', 'Attendance imported successfully.');
    }

    public function addParticipant(Request $request)
    {
        $request->validate([
            'event_id' => 'exists:events,id',
            'unit_id.*' => 'required|exists:units,id',
        ]);

        $event = Event::find($request->event_id);
        $event->units()->syncWithoutDetaching($request->unit_id);

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
}
