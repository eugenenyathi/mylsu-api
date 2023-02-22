<?php

namespace Database\Seeders;

use App\Models\RoomRange;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomRangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */


    public function run()
    {
        $ranges = $this->roomRanges();
        foreach ($ranges as $range) {
            RoomRange::create($range);
        }
    }

    private function roomRanges()
    {
        $data = [

            [
                'first_room' => 101,
                'last_room' => 115,
                'side' => 'M',
                'floor' => '1st',
                'suburb_floor_side' => 'Left',
                'mbundani_floor_side' => 'Right',
            ],
            [
                'first_room' => 116,
                'last_room' => 130,
                'side' => 'M',
                'floor' => '1st',
                'suburb_floor_side' => 'Right',
                'mbundani_floor_side' => 'Left',
            ],
            [
                'first_room' => 131,
                'last_room' => 145,
                'side' => 'F',
                'floor' => '1st',
                'suburb_floor_side' => 'Left',
                'mbundani_floor_side' => 'Right',
            ],
            [
                'first_room' => 146,
                'last_room' => 160,
                'side' => 'F',
                'floor' => '1st',
                'suburb_floor_side' => 'Right',
                'mbundani_floor_side' => 'Left',
            ],
            [
                'first_room' => 201,
                'last_room' => 215,
                'side' => 'M',
                'floor' => '2nd',
                'suburb_floor_side' => 'Left',
                'mbundani_floor_side' => 'Right',
            ],
            [
                'first_room' => 216,
                'last_room' => 230,
                'side' => 'M',
                'floor' => '2nd',
                'suburb_floor_side' => 'Right',
                'mbundani_floor_side' => 'Left',
            ],
            [
                'first_room' => 231,
                'last_room' => 245,
                'side' => 'F',
                'floor' => '2nd',
                'suburb_floor_side' => 'Left',
                'mbundani_floor_side' => 'Right',
            ],
            [
                'first_room' => 246,
                'last_room' => 260,
                'side' => 'F',
                'floor' => '2nd',
                'suburb_floor_side' => 'Right',
                'mbundani_floor_side' => 'Left',
            ],
            [
                'first_room' => 301,
                'last_room' => 315,
                'side' => 'M',
                'floor' => '3rd',
                'suburb_floor_side' => 'Left',
                'mbundani_floor_side' => 'Right',
            ],
            [
                'first_room' => 316,
                'last_room' => 330,
                'side' => 'M',
                'floor' => '3rd',
                'suburb_floor_side' => 'Right',
                'mbundani_floor_side' => 'Left',
            ],
            [
                'first_room' => 331,
                'last_room' => 345,
                'side' => 'F',
                'floor' => '3rd',
                'suburb_floor_side' => 'Left',
                'mbundani_floor_side' => 'Right',
            ],
            [
                'first_room' => 346,
                'last_room' => 360,
                'side' => 'F',
                'floor' => '3rd',
                'suburb_floor_side' => 'Right',
                'mbundani_floor_side' => 'Left',
            ],

        ];

        return $data;
    }
}
