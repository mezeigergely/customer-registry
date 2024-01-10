<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $events = [
            //2023-09-08-án 8-10 óra
            [
                'title' => 'Esemény 1 a Seederből',
                'start' => '2023-09-08T08:00:00.000Z',
                'end' => '2023-09-08T10:00:00.000Z',
                'rrule' => null,
                'duration' => '02:00',
            ],
            //2023-01-01-től minden páros héten hétfőn 10-12 óra
            [
                'title' => 'Esemény 2 a Seederből',
                'start' => '2023-01-01T10:00:00.000Z',
                'end' => '2023-01-01T12:00:00.000Z',
                'rrule' => 'DTSTART:20230101T100000\nRRULE:FREQ=WEEKLY;INTERVAL=2;BYDAY=MO',
                'duration' => '02:00',
            ],
            //2023-01-01-től minden páratlan héten szerda 12-16 óra
            [
                'title' => 'Esemény 3 a Seederből',
                'start' => '2023-01-01T12:00:00.000Z',
                'end' => '2023-01-01T16:00:00.000Z',
                'rrule' => 'DTSTART:20230102T120000\nRRULE:FREQ=WEEKLY;INTERVAL=2;BYDAY=WE',
                'duration' => '04:00',
            ],
            //2023-01-01-től minden héten pénteken 10-16 óra
            [
                'title' => 'Esemény 4 a Seederből',
                'start' => '2023-01-01T10:00:00.000Z',
                'end' => '2023-01-01T16:00:00.000Z',
                'rrule' => 'DTSTART:20230101T100000\nRRULE:FREQ=WEEKLY;INTERVAL=1;BYDAY=FR',
                'duration' => '06:00',
            ],
            //2023-06-01-től 2023-11-30-ig minden héten csütörtökön 16-20 óra
            [
                'title' => 'Esemény 5 a Seederből',
                'start' => '2023-06-01T16:00:00.000Z',
                'end' => '2023-06-01T20:00:00.000Z',
                'rrule' => 'DTSTART:20230601T000000\nRRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=20231130T000000;BYDAY=TH',
                'duration' => '04:00',
            ],

        ];

        foreach ($events as $eventData) {
            Event::create($eventData);
        }
    }
}
