<?php

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FacultiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faculties = [
            [
                'id' => 'AGR',
                'faculty' => 'Agricultural Sciences'
            ],
            [
                'id' => 'ENG',
                'faculty' => 'Engineering'
            ],
            [
                'id' => 'HUN',
                'faculty' => 'Humanities'
            ],
            [
                'id' => 'COM',
                'faculty' => 'Commerce'
            ]
        ];

        foreach ($faculties as $faculty) {
            Faculty::create($faculty);
        }
    }
}
