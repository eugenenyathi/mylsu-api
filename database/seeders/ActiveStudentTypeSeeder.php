<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActiveStudentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ActiveStudentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'student_type' => 'con',
                'active' => 'Yes'
            ],
            [
                'student_type' => 'block',
                'active' => 'No'
            ]
        ];


        foreach ($data as $input) {
            ActiveStudentType::create($input);
        }
    }
}
