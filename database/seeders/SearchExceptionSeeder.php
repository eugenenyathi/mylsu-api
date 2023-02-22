<?php

namespace Database\Seeders;

use App\Models\SearchException;
use Illuminate\Database\Seeder;
use App\Models\ActiveStudentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SearchExceptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $programs = [
            ['program_id' => 'PRD31'],
            ['program_id' => 'PRD32']
        ];

        foreach ($programs as $program) {
            SearchException::create($program);
        }
    }
}
