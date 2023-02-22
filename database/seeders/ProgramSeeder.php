<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Traits\ProgramsMall;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    use ProgramsMall;

    public function run()
    {
        $programmes = $this->main();

        foreach ($programmes as $program) {

            $exists = Program::where('id', $program['id'])->exists();

            if ($exists) continue;
            else {
                Program::create($program);
            }
        }
    }
}
