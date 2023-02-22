<?php

namespace Database\Seeders;

use App\Models\HostelFees;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HostelFeesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $hostelFees = [
            [
                'student_type' => 'Block',
                'fee' => 20000
            ],
            [
                'student_type' => 'Conventional',
                'fee' => 40000
            ]
        ];


        foreach ($hostelFees as $fee) {
            HostelFees::create($fee);
        }
    }
}
