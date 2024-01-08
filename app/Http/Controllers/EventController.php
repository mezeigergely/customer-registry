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
        $events->transform(function ($event) {
            if ($event->daysOfWeek === null) {
                unset($event->daysOfWeek, $event->startTime, $event->endTime);
            }
            return $event;
        });
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

            $title = $request->input('title');
            $start = $request->input('start');
            $end = $request->input('end');
            $daysOfWeek = $request->daysOfWeek;
            foreach ($daysOfWeek as $day){
                if(empty($day)){
                    $daysOfWeek = NULL;
                }
                else{
                    $daysOfWeek = json_encode($request->daysOfWeek);
                }
            }
            $startTime = Carbon::parse($start)->format('H:i:s');
            $endTime = Carbon::parse($end)->format('H:i:s');

            $slotTimeChecker = $this->isValidSlotTime($start, $end);

            if($slotTimeChecker){
                $event = new Event();
                $event->title = $title;
                $event->start = $start;
                $event->end = $end;
                $event->startTime = $startTime;
                $event->endTime = $endTime;
                $event->daysOfWeek = $daysOfWeek;
                $event->save();
                return response()->json([
                    'message' => $request->input('title').
                    ' nevű időpontja rögzítésre került!'], 200
                );
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
        //TODO: szabály kidolgozás az ismétlődésekhez!
        return !Event::where(function ($query) use ($start, $end) {
            $query->where('start', '<', $end)
                  ->where('end', '>', $start);
        })->exists();
    }
}
