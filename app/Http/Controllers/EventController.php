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
    protected EventTransformer $eventTransformer;

    public function __construct(
        EventRepository $eventRepository,
        EventService $eventService,
        EventTransformer $eventTransformer)
    {
        $this->eventRepository = $eventRepository;
        $this->eventService = $eventService;
        $this->eventTransformer = $eventTransformer;
    }

    public function index()
    {
        $events = $this->eventRepository->getAll();
        $transformedEvents = $this->eventTransformer->transformEventForFrontend($events);

        return response()->json($transformedEvents);
    }

    public function createEvent(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'start' => 'required|string|max:255',
                'end' => 'required|string|max:255',
            ]);

            $start = $request->input('start');
            $end = $request->input('end');

            $slotTimeChecker = $this->eventService->isValidSlotTime($start, $end);

            if($slotTimeChecker){
                $title = $request->input('title');
                $freq = $request->input('freq');
                $interval = 1;
                $specWeek = $request->input('specWeek');
                $byDay = $request->input('byDay') ?: null;

                if($this->eventService->createEvent(
                        $title,
                        $start,
                        $end,
                        $freq,
                        $interval,
                        $specWeek,
                        $byDay))
                {
                    return response()->json([
                        'message' => 'Sikeres bejegyzés'], 200
                    );
                }
            }

            return response()->json(['message' => 'Hiba!'], 400);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hiba', 'details' => $e]);
        }
    }

    
}
