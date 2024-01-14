<?php

namespace App\Repositories;

use App\Models\Event;

class EventRepository
{
    function getAll()
    {
        return Event::all();
    }

    function isSlotReserved($start, $end)
    {
        return !Event::where(function ($query) use ($start, $end) {
            $query->where('start', '<', $end)
                  ->where('end', '>', $start);
        })->exists();
    }
}
