<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\EventRepository;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class EventService
{
    protected EventRepository $eventRepository;
    protected string $openingTime;
    protected string $closingTime;

    function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->openingTime = Config::get('working_hours.opening_time');
        $this->closingTime = Config::get('working_hours.closing_time');
    }

    function createEvent(array $eventData): bool
    {
        $title = $eventData['title'];
        $start = $eventData['start'];
        $end = $eventData['end'];
        $recurrance = $eventData['recurrance'];
        $carbonStart = Carbon::parse($start);
        $carbonEnd = Carbon::parse($end);
        $until = $eventData['until'];
        
        if($this->isSameDay($carbonStart, $carbonEnd)){
            $carbonUntil = Carbon::parse($until);

            if($recurrance){
                $eventRequests = array();

                switch($recurrance){
                    case 'EVEN_WEEKLY':
                        if(!$this->isEvenWeek($carbonStart)){
                            $carbonStart = $carbonStart->addWeek();
                            $carbonEnd = $carbonEnd->addWeek();
                            $start = $carbonStart->format('Y-m-d\TH:i:s.v\Z');
                            $end = $carbonEnd->format('Y-m-d\TH:i:s.v\Z');
                        }

                        $eventRequestData = $this->createEventRequestData($start, $end, $carbonUntil);
                        $eventRequests = $this->fillEventRequests($eventRequestData, 2, 0);
                        $success = false;

                        foreach ($eventRequests as $eventRequest){
                            if (!$this->isDateAvailable($eventRequest['start'], $eventRequest['end'])){
                                break;
                            }
                            else {
                                Event::create([
                                    'title' => $eventData['title'],
                                    'start' => $eventRequest['start'],
                                    'end' => $eventRequest['end'],
                                    'until' => $until,
                                    'recurrance' => $recurrance,
                                    'day' => $eventRequest['day'],
                                    'inside_day' => $eventRequest['inside_day'],
                                ]);
                                $success = true;
                            }
                        }

                        if($success) {
                            return true;
                        }

                        return false;
                        break;
                    
                    case 'ODD_WEEKLY':
                        if($this->isEvenWeek($carbonStart)){
                            $carbonStart = $carbonStart->addWeek();
                            $carbonEnd = $carbonEnd->addWeek();
                            $start = $carbonStart->format('Y-m-d\TH:i:s.v\Z');
                            $end = $carbonEnd->format('Y-m-d\TH:i:s.v\Z');
                        }

                        $eventRequestData = $this->createEventRequestData($start, $end, $carbonUntil);
                        $eventRequests = $this->fillEventRequests($eventRequestData, 2, 0);
                        $success = false;

                        foreach ($eventRequests as $eventRequest){
                            if (!$this->isDateAvailable($eventRequest['start'], $eventRequest['end'])){
                                break;
                            }
                            else {
                                Event::create([
                                    'title' => $eventData['title'],
                                    'start' => $eventRequest['start'],
                                    'end' => $eventRequest['end'],
                                    'until' => $until,
                                    'recurrance' => $recurrance,
                                    'day' => $eventRequest['day'],
                                    'inside_day' => $eventRequest['inside_day'],
                                ]);
                                $success = true;
                            }
                        }

                        if($success) {
                            return true;
                        }

                        return false;
                        break;
                    
                    default:
                        $eventRequestData = $this->createEventRequestData($start, $end, $carbonUntil);
                        $eventRequests = $this->fillEventRequests($eventRequestData, 1, 1);
                        $success = false;

                        foreach ($eventRequests as $eventRequest){
                            if (!$this->isDateAvailable($eventRequest['start'], $eventRequest['end'])){
                                break;
                            }
                            else {
                                Event::create([
                                    'title' => $eventData['title'],
                                    'start' => $eventRequest['start'],
                                    'end' => $eventRequest['end'],
                                    'until' => $until,
                                    'recurrance' => $recurrance,
                                    'day' => $eventRequest['day'],
                                    'inside_day' => $eventRequest['inside_day'],
                                ]);
                                $success = true;
                            }
                        }

                        if($success){
                            return true;
                        }

                        return false;
                }
            }
            if($this->isDateAvailable($start, $end)){
                Event::create([
                    'title' => $title,
                    'start' => $start,
                    'end' => $end,
                    'until' => $end,
                    'recurrance' => $recurrance,
                    'day' => $carbonStart->getTranslatedDayName(),
                    'inside_day' => $carbonStart->format('H:i:s').' - '.$carbonEnd->format('H:i:s'),
                ]);
                return true;
            }
        }
        
        return false;   
    }

    static function isSameDay(Carbon $start, Carbon $end)
    {
        return $start->format('Y-m-d') == $end->format('Y-m-d');
    }

    static function isEvenWeek(Carbon $date) : bool
    {
        $weekNumber = $date->weekOfYear;
        return $weekNumber % 2 === 0;
    }

    protected function createEventRequestData(string $start, string $end, Carbon $until)
    {
        return ['start' => $start,'end' => $end,'until' => $until];
    }

    protected function fillEventRequests(array $eventRequestData, int $addWeekValue, int $totalIteration) : array
    {
        $carbonStart = Carbon::parse($eventRequestData['start']);
        $carbonEnd = Carbon::parse($eventRequestData['end']);
        $carbonUntil = $eventRequestData['until'];

        $eventRequests = array();
        $weeksBetweenStartAndUntil = $carbonUntil->diffInWeeks($carbonStart);
        for ($i = 0 ; $i < ($weeksBetweenStartAndUntil + $totalIteration); $i++)
        {
            if($carbonStart < $carbonUntil){
                $eventRequests[] = [
                    'start' => $eventRequestData['start'],
                    'end' => $eventRequestData['end'],
                    'day' => $carbonStart->getTranslatedDayName(),
                    'inside_day' => $carbonStart->format('H:i:s').' - '.$carbonEnd->format('H:i:s')
                ];
                $carbonStart->addWeek($addWeekValue);
                $carbonEnd->addWeek($addWeekValue);
                $eventRequestData['start'] = $carbonStart->format('Y-m-d\TH:i:s.v\Z');
                $eventRequestData['end'] = $carbonEnd->format('Y-m-d\TH:i:s.v\Z');
            }
        }
        
        return $eventRequests;
    }

    protected function isDateAvailable(string $start, string $end) : bool
    {
        $startTimeChecker = $this->isWithinTheOpeningHours($start);
        $endTimeChecker = $this->isWithinTheOpeningHours($end);
   
        if($startTimeChecker && $endTimeChecker){
            return $this->eventRepository->isSlotReserved($start, $end);
        }

        return false;
    }

    protected function isWithinTheOpeningHours(string $date) : bool
    {
        $carbonDate = Carbon::parse($date);
        $result = $carbonDate->between(
            $carbonDate->toDateString() . ' ' . $this->openingTime,
            $carbonDate->toDateString() . ' ' . $this->closingTime
        );

        return $result;
    }
}