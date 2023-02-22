<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\OldResidence;
use App\Models\Profile;
use App\Traits\FakeCredentials;
use App\Traits\Utils;
use App\Traits\VUtils;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OldResidenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */



    use Utils;

    private $hostel;
    private $side;
    private $floor;
    private $floorSide;
    private $room;
    private $part;


    public function run()
    {
        //get all students
        $students = $this->fetchStudents();

        foreach ($students as $student) {
            $this->init($student->id);

            OldResidence::create([
                'student_id' => $student->id,
                'hostel' => $this->hostel,
                'room' => $this->room,
                'part' => $this->part,
            ]);
        }
    }

    private function init($studentID)
    {
        $hostels = ['Suburb', 'Mbundani'];
        //get hostel
        $this->hostel = $hostels[$this->random(0, count($hostels) - 1)];
        //get room
        $this->room = $this->room($studentID);
        //set part
        $this->part = $this->getPart($studentID);
    }

    private function room($studentID)
    {
        $gender = $this->gender($studentID);

        while (true) {
            $room = $this->random(101, 345);

            if ($room >= 161 && $room <= 200) {
                continue;
            } elseif ($room >= 261 && $room <= 300) {
                continue;
            } else {

                if ($gender === 'Male') return $room;

                if ($room >= 131 && $room <= 160) {
                    return $room;
                } elseif ($room >= 231 && $room <= 260) {
                    return $room;
                } elseif ($room >= 331 && $room <= 360) {
                    return $room;
                }
            }
        }

        return $room;
    }


    private function getPart($studentID)
    {
        $studentPart = $this->part($studentID);
        $previousLevel = $this->previousLevel($studentPart);

        return $previousLevel;
    }

    private function previousLevel($studentPart)
    {

        $excepted = [];

        switch ($studentPart) {
            case 4.2:
                return 4.1;
            case 4.1:
                return 2.2;
            case 3.2:
                return 2.2;
            case 3.1:
                return 2.2;
            case 2.2:
                return 2.1;
            case 2.1:
                return 1.2;
            case 1.2:
                return 1.1;
        }
    }

    //students without accounts
    private function fetchStudents()
    {
        $students = Student::select('id')->get();
        $virgins = [];

        foreach ($students as $student) {
            $hasAccount = OldResidence::where('student_id', $student->id)->exists();

            if ($hasAccount) continue;
            else $virgins[] = $student;
        }

        return $virgins;
    }
}
