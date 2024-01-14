<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        //2023-09-08-án 8-10 óra
        Event::create([
            'title' => 'Esemény 1 a Seederből',
            'start' => '2023-09-08T08:00:00.000Z',
            'end' => '2023-09-08T10:00:00.000Z',
            'until' => '2023-09-08T10:00:00.000Z',
            'day' => 'Friday',
            'inside_day' => '08:00:00 - 10:00:00'
        ]);

        //2023-01-01-től minden páros héten hétfőn 10-12 óra
        $start = '2023-01-09T10:00:00.000Z';
        $end = '2023-01-09T12:00:00.000Z';
        $carbonStart = Carbon::parse($start);
        $carbonEnd = Carbon::parse($end);

        for($i = 0; $i < 26; $i++){
            Event::create([
                'title' => 'Esemény 2 a Seederből',
                'start' => $start,
                'end' => $end,
                'until' => '2023-12-31T23:59:00.000Z',
                'recurrance' => 'EVEN_WEEKLY',
                'day' => 'Monday',
                'inside_day' => '10:00:00 - 12:00:00'
            ]);
            $carbonStart = $carbonStart->addWeek(2);
            $carbonEnd = $carbonEnd->addWeek(2);
            $start = $carbonStart->format('Y-m-d\TH:i:s.v\Z');
            $end = $carbonEnd->format('Y-m-d\TH:i:s.v\Z');
        }

        //2023-01-01-től minden páratlan héten szerda 12-16 óra
        $start = '2023-01-04T12:00:00.000Z';
        $end = '2023-01-04T16:00:00.000Z';
        $carbonStart = Carbon::parse($start);
        $carbonEnd = Carbon::parse($end);

        for($i = 0; $i < 26; $i++){
            Event::create([
                'title' => 'Esemény 3 a Seederből',
                'start' => $start,
                'end' => $end,
                'until' => '2023-12-31T23:59:00.000Z',
                'recurrance' => 'ODD_WEEKLY',
                'day' => 'Wednesday',
                'inside_day' => '12:00:00 - 16:00:00'
            ]);
            $carbonStart = $carbonStart->addWeek(2);
            $carbonEnd = $carbonEnd->addWeek(2);
            $start = $carbonStart->format('Y-m-d\TH:i:s.v\Z');
            $end = $carbonEnd->format('Y-m-d\TH:i:s.v\Z');
        }

        //2023-01-01-től minden héten pénteken 10-16 óra
        $start = '2023-01-05T10:00:00.000Z';
        $end = '2023-01-05T16:00:00.000Z';
        $carbonStart = Carbon::parse($start);
        $carbonEnd = Carbon::parse($end);

        for($i = 0; $i < 52; $i++){
            Event::create([
                'title' => 'Esemény 4 a Seederből',
                'start' => $start,
                'end' => $end,
                'until' => '2023-12-31T23:59:00.000Z',
                'recurrance' => 'WEEKLY',
                'day' => 'Friday',
                'inside_day' => '10:00:00 - 16:00:00'
            ]);
            $carbonStart = $carbonStart->addWeek();
            $carbonEnd = $carbonEnd->addWeek();
            $start = $carbonStart->format('Y-m-d\TH:i:s.v\Z');
            $end = $carbonEnd->format('Y-m-d\TH:i:s.v\Z');
        }

        //2023-06-01-től 2023-11-30-ig minden héten csütörtökön 16-20 óra
        $start = '2023-06-01T16:00:00.000Z';
        $end = '2023-06-01T20:00:00.000Z';
        $carbonStart = Carbon::parse($start);
        $carbonEnd = Carbon::parse($end);

        for($i = 0; $i < 27; $i++){
            Event::create([
                'title' => 'Esemény 5 a Seederből',
                'start' => $start,
                'end' => $end,
                'until' => '2023-11-30T00:00:00.000Z',
                'recurrance' => 'WEEKLY',
                'day' => 'Thursday',
                'inside_day' => '16:00:00 - 20:00:00'
            ]);
            $carbonStart = $carbonStart->addWeek();
            $carbonEnd = $carbonEnd->addWeek();
            $start = $carbonStart->format('Y-m-d\TH:i:s.v\Z');
            $end = $carbonEnd->format('Y-m-d\TH:i:s.v\Z');
        }
    }
}
