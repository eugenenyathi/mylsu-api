<?php

namespace Database\Seeders;

use App\Models\Tuition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TuitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tuition = [
            [
                'faculty_id' => 'AGR',
                'con_amount' => 270000,
                'block_amount' => 135000
            ],
            [
                'faculty_id' => 'ENG',
                'con_amount' => 270000,
                'block_amount' => 135000
            ],
            [
                'faculty_id' => 'HUN',
                'con_amount' => 260000,
                'block_amount' => 132000
            ],
            [
                'faculty_id' => 'COM',
                'con_amount' => 265000,
                'block_amount' => 132500
            ]
        ];


        foreach ($tuition as $record) {
            Tuition::create($record);
        }
    }
}
