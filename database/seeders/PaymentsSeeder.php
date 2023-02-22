<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Profile;
use App\Models\Student;
use App\Traits\FakeCredentials;
use App\Traits\Utils;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PaymentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    use Utils;

    public function run()
    {
        //get all students
        $students = $this->fetchStudents();

        // dd(count($students));

        foreach ($students as $student) {

            Payment::create([
                'student_id' => $student->id,
                'amount_cleared' => $this->amountCleared($student->id),
                'registered' => 1,
            ]);
        }
    }

    //students without accounts
    private function fetchStudents()
    {
        $students = Student::select('id')->get();
        $virgins = [];

        foreach ($students as $student) {
            $hasAccount = Payment::where('student_id', $student->id)->exists();

            if ($hasAccount) continue;
            else $virgins[] = $student;
        }

        return $virgins;
    }

    private function amountCleared($studentID)
    {
        $studentType = $this->studentType($studentID);

        if ($studentType === "Conventional") {
            return $this->random(150000, 250000);
        } else return $this->random(70000, 135000);
    }
}
