<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Exception;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::all();

        return response()->json($events);
    }

    public function createEvent(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'start' => 'required|string|max:255',
                'end' => 'required|string|max:255',
            ]);
    
            $event = new Event();
            $event->title = $request->input('title');
            $event->start = $request->input('start');
            $event->end = $request->input('end');
    
            $event->save();
    
            return response()->json(['message' => $request->input('title').' nevű időpontja rögzítésre került!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hiba', 'details' => $e]);
        }
    }
}
