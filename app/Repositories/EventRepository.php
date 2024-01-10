<?php

namespace App\Repositories;

use App\Models\Event;

class EventRepository
{
    public function getAll()
    {
        return Event::select('title', 'start', 'end', 'rrule', 'duration')->get();
    }

    public function isSlotReserved($start, $end)
    {
        return !Event::where(function ($query) use ($start, $end) {
            $query->where('start', '<', $end)
                  ->where('end', '>', $start);
        })->exists();
    }
}
