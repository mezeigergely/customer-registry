<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
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

            $slotTimeChecker = $this->isValidSlotTime($request->input('start'), $request->input('end'));

            if($slotTimeChecker){
                $event = new Event();
                $event->title = $request->input('title');
                $event->start = $request->input('start');
                $event->end = $request->input('end');
                $event->save();
                return response()->json(['message' => $request->input('title').' nevű időpontja rögzítésre került!'], 200);
            }

            return response()->json(['message' => 'Hiba!'], 400);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Hiba', 'details' => $e]);
        }
    }

    protected function isValidSlotTime($start, $end)
    {
        $openingTime = Config::get('working_hours.opening_time');
        $closingTime = Config::get('working_hours.closing_time');

        $carbonStart = Carbon::parse($start);
        $carbonEnd = Carbon::parse($end);

        $startInWorkingHours = $carbonStart->between(
            Carbon::parse($carbonStart->toDateString() . ' ' . $openingTime),
            Carbon::parse($carbonStart->toDateString() . ' ' . $closingTime)
        );
    
        $endInWorkingHours = $carbonEnd->between(
            Carbon::parse($carbonEnd->toDateString() . ' ' . $openingTime),
            Carbon::parse($carbonEnd->toDateString() . ' ' . $closingTime)
        );
    
        if($startInWorkingHours && $endInWorkingHours){
            return $this->isSlotReserved($start, $end);
        }
        return false;
    }

    protected function isSlotReserved($start, $end)
    {
        return !Event::where(function ($query) use ($start, $end) {
            $query->where('start', '<', $end)
                  ->where('end', '>', $start);
        })->exists();
    }
}
