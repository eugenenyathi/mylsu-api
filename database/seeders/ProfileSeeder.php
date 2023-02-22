<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Student;
use Illuminate\Support\Str;
use App\Traits\ProgramsMall;
use App\Traits\Utils;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    private $part;
    private $programID;
    private $enrolled;
    private $studentType = 'Conventional';
    private $programStudentType = 'con';

    use Utils;
    use ProgramsMall;

    public function run()
    {

        //get all students who don't have profile
        $students = $this->fetchStudents();

        $counter = 1;

        foreach ($students as $student) {

            //for every 4 conventional students create 1 block
            if ($counter > 4) {
                $this->studentType = 'Block';
                $this->programStudentType = 'block';
                $counter = 1;
            } else {
                $this->studentType = 'Conventional';
                $this->programStudentType = 'con';
            }

            //setting values to the properties
            $this->init();

            Profile::create([
                'student_id' => $student->id,
                'program_id' => $this->programID,
                'part' => $this->part,
                'student_type' => $this->studentType,
                'enrolled' => $this->enrolled
            ]);

            $counter++;
        }
    }


    private function init()
    {

        /**
         * 1. Allocate a random part/level 
         * 2. Set the enrollment year
         * 3. Match that part with an appropriate program level
         */

        // this determines how many parts will you hv, you can reduce
        // them to allow for better or focused testing
        $levels  = [1.2, 2.1, 2.2, 3.1, 4.1, 4.2];

        //these are restricted so to allow for better testing
        $blockLevels = [1.2, 2.1, 2.2];

        //Getting a random level/part
        $this->part = $levels[$this->random(0, count($levels) - 1)];

        if ($this->studentType === 'Block')
            $this->part = $blockLevels[$this->random(0, count($blockLevels) - 1)];

        //this is the function that creates all the program codes and stuff
        //its found in the traits folder
        $this->main();

        switch ($this->part) {
            case 1.2:
                //this function matches the student part with a corresponding program code part
                $programs = $this->levelProgramCodes($this->programStudentType, 12);
                $this->enrolled = 2022;
                //Get a random program id
                $this->programID = $this->randomProgramID($programs);
                break;
            case 2.1:
                $programs = $this->levelProgramCodes($this->programStudentType, 21);
                $this->enrolled = 2021;
                //Get a random program id
                $this->programID = $this->randomProgramID($programs);
                break;
            case 2.2:
                $programs = $this->levelProgramCodes($this->programStudentType, 22);
                $this->enrolled = 2021;
                //Get a random program id
                $this->programID = $this->randomProgramID($programs);
                break;
            case 3.1:
                $programs = $this->levelProgramCodes($this->programStudentType, 31);
                $this->enrolled = 2020;
                //Get a random program id
                $this->programID = $this->randomProgramID($programs);
                break;
            case 3.2:
                $programs = $this->levelProgramCodes($this->programStudentType, 32);
                $this->enrolled = 2020;
                //Get a random program id
                $this->programID = $this->randomProgramID($programs);
                break;
            case 4.1:
                $programs = $this->levelProgramCodes($this->programStudentType, 41);
                $this->enrolled = 2019;
                //Get a random program id
                $this->programID = $this->randomProgramID($programs);
                break;
            case 4.2:
                $programs = $this->levelProgramCodes($this->programStudentType, 42);
                $this->enrolled = 2019;
                //Get a random program id
                $this->programID = $this->randomProgramID($programs);
                break;
        }
    }


    //students without accounts
    private function fetchStudents()
    {
        $students = Student::select('id')->get();
        $virgins = [];

        foreach ($students as $student) {
            $hasAccount = Profile::where('student_id', $student->id)->exists();

            if ($hasAccount) continue;
            else $virgins[] = $student;
        }

        return $virgins;
    }

    private function randomProgramID($programs)
    {
        return $programs[$this->random(0, count($programs) - 1)];
    }
}
