<?php

namespace App\Transformers;

class EventTransformer
{
    public function transformEventForFrontend($events)
    {
        $events->transform(function ($event) {
            if ($event->rrule === null) {
                unset($event->rrule, $event->duration);
            }
            else{
                unset($event->start, $event->end);
                $stripslashesRrule = stripslashes($event->rrule);
                $event->rrule = $stripslashesRrule;
                $event->rrule = str_replace("nRRULE:", "\nRRULE:", $event->rrule);
            }
            return $event;
        });
        return $events;
    }

    public function convertToDtstart($time, $inclTime = true)
    {
        return date('Ymd' . ($inclTime ? '\THis' : ''), $time);
    }
}
