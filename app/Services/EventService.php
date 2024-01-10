<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\EventRepository;
use App\Transformers\EventTransformer;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class EventService
{
    protected EventRepository $eventRepository;
    protected EventTransformer $eventTransformer;
    protected string $openingTime;
    protected string $closingTime;

    public function __construct(
        EventRepository $eventRepository,
        EventTransformer $eventTransformer
    ) {
        $this->eventRepository = $eventRepository;
        $this->eventTransformer = $eventTransformer;
        $this->openingTime = Config::get('working_hours.opening_time');
        $this->closingTime = Config::get('working_hours.closing_time');
    }

    public function createEvent(
        string $title,
        string $start,
        string $end,
        ?string $freq,
        int $interval,
        ?string $specWeek,
        ?array $byDay
    ): bool {
        $carbonStart = Carbon::parse($start);
        $carbonEnd = Carbon::parse($end);
        $duration = $this->calcDuration($start, $end);

        if(isset($specWeek)){
            $weekOfYear = $carbonStart->weekOfYear;
            switch($specWeek){
                case 'ODD_WEEKLY':
                    if($weekOfYear % 2 == 0){
                        $this->saveSimpleEvent($title,$start,$end,$duration);
                        $carbonStart->addWeek();
                        $carbonEnd->addWeek();
                    }
                    break;

                case 'EVEN_WEEKLY':
                    if($weekOfYear % 2 != 0){
                        $this->saveSimpleEvent($title,$start,$end,$duration);
                        $carbonStart->addWeek();
                        $carbonEnd->addWeek();
                    }
                    break;
            }
            $interval = 2;
            $start = $carbonStart->format('Y-m-d\TH:i:s.v\Z');
            $end = $carbonEnd->format('Y-m-d\TH:i:s.v\Z');
        }

        $rrule = $this->generateRRule($freq, $carbonStart, $interval, $byDay);

        $newEvent = $this->saveEvent($title, $start, $end, $rrule, $duration);

        return $newEvent;
    }

    protected function saveSimpleEvent(string $title, string $start, string $end, string $duration)
    {
        $this->saveEvent($title, $start, $end, null, $duration);
    }

    protected function saveEvent(string $title, string $start, string $end, ?string $rrule, string $duration) : bool
    {
        $event = new Event();
        $event->title = $title;
        $event->start = $start;
        $event->end = $end;
        $event->rrule = $rrule;
        $event->duration = $duration;
        $event->save();

        return true;
    }

    public function isValidSlotTime(string $start, string $end) : bool
    {
        $carbonStart = Carbon::parse($start);
        $carbonEnd = Carbon::parse($end);

        $startInWorkingHours = $carbonStart->between(
            Carbon::parse($carbonStart->toDateString() . ' ' . $this->openingTime),
            Carbon::parse($carbonStart->toDateString() . ' ' . $this->closingTime)
        );
    
        $endInWorkingHours = $carbonEnd->between(
            Carbon::parse($carbonEnd->toDateString() . ' ' . $this->openingTime),
            Carbon::parse($carbonEnd->toDateString() . ' ' . $this->closingTime)
        );
    
        if($startInWorkingHours && $endInWorkingHours){
            return $this->eventRepository->isSlotReserved($start, $end);
        }

        return false;
    }

    protected function calcDuration(string $start, string $end) : string
    {
        $startTime = Carbon::parse($start);
        $endTime = Carbon::parse($end);

        return $endTime->diff($startTime)->format('%H:%I:%S');
    }

    protected function generateRRule(?string $freq, Carbon $carbonStart, int $interval, ?array $byDay): ?string
    {
        if ($freq === null) {
            return null;
        }

        $rrule = "DTSTART:" . $this->eventTransformer->convertToDtstart($carbonStart->timestamp);
        $rrule .= "\\nRRULE:FREQ=" . $freq . ";INTERVAL=" . $interval;

        if ($byDay !== null) {
            $rrule .= ';BYDAY=';
            $count = count($byDay);
            $i = 0;

            foreach ($byDay as $day) {
                $rrule .= $day;
                if (++$i < $count) {
                    $rrule .= ',';
                }
            }
        }

        return $rrule;
    }
}