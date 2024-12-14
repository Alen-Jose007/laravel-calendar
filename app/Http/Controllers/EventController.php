<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;


class EventController extends Controller
{
    // Fetch all events
    public function index()
    {
        try {
            $events = Event::all();
            return response()->json($events);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not fetch events'], 500);
        }
    }

    // Store a new event
    public function store(Request $request)
    {
        // Validate incoming request
         $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'description' => 'nullable|string', // Add description validation
         ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Parse the start and end dates using Carbon
            $start = Carbon::parse($request->start);
            $end = Carbon::parse($request->end);

            // Create the event
            $event = Event::create([
                'title' => $request->title,
                'description' => $request->description, // Save description
                'start' => $start,
                'end' => $end,
            ]);

            return response()->json($event, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not create event', 'message' => $e->getMessage()], 500);
        }
    }

    // Update an existing event
    public function update(Request $request, $id)
    {
         // Validate incoming request
         $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'description' => 'nullable|string', // Add description validation
         ]);
         
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $event = Event::findOrFail($id);

            // Parse the start and end dates using Carbon
            $start = Carbon::parse($request->start);
            $end = Carbon::parse($request->end);

            // Update the event
            $event->update([
                'title' => $request->title,
                'description' => $request->description, // Update description
                'start' => $start,
                'end' => $end,
            ]);

            return response()->json($event);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not update event', 'message' => $e->getMessage()], 500);
        }
    }

    // Delete an event
    public function destroy($id)
    {
        try {
            $event = Event::findOrFail($id);
            $event->delete();

            return response()->json(['message' => 'Event deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not delete event', 'message' => $e->getMessage()], 500);
        }
    }
}