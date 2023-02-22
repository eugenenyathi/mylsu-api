<?php

namespace Database\Seeders;

use App\Models\CheckInOut;
use App\Models\Residence;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CheckInOutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $checkInOut =
            [
                [
                    'type' => 'checkIn',
                    'con_students_date' => '2023-02-26',
                    'block_students_date' => '2023-04-16'
                ],
                [
                    'type' => 'checkOut',
                    'con_students_date' => '2023-04-16',
                    'block_students_date' => '2023-05-13',
                ]
            ];


        foreach ($checkInOut as $record) {
            CheckInOut::create($record);
        }
    }
}
