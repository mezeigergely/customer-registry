<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\EventRepository;
use App\Services\EventService;
use App\Transformers\EventTransformer;

class EventController extends Controller
{
    protected EventRepository $eventRepository;
    protected EventService $eventService;

    public function __construct(
        EventRepository $eventRepository,
        EventService $eventService)
    {
        $this->eventRepository = $eventRepository;
        $this->eventService = $eventService;
    }

    function index()
    {
        $events = $this->eventRepository->getAll();
        return response()->json($events);
    }

    function createEvent(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'start' => 'required|string|max:255',
                'end' => 'required|string|max:255'
            ]);

            $eventData = [
                'title' => $request->input('title'),
                'start' => $request->input('start'),
                'end' => $request->input('end'),
                'until' => $request->input('until'),
                'recurrance' => $request->input('recurrance')
            ];

            if($this->eventService->createEvent($eventData)){
                return response()->json([
                    'message' => 'Sikeres bejegyzés'], 200
                );
            }

            return response()->json(['message' => 'Hiba!'], 400);

        } catch (\Exception $e) {
            return response()->json(['message' => $e], 400);
        }
    }
}