<?php

namespace App\Http\Controllers;

use App\Traits\Utils;
use App\Models\Profile;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\DB;

class UtilsController extends Controller
{
    use HttpResponses;
    use Utils;

    public function randomStudent()
    {
        //get student numbers
        $conStudentIDs = Profile::select('student_id')
            ->where('student_type', 'Conventional')
            ->get();

        $blockStudentIDs = Profile::select('student_id')
            ->where('student_type', 'Block')
            ->get();


        //get random student id
        $conStudentID = $conStudentIDs[$this->random(0, count($conStudentIDs) - 1)];
        $blockStudentID = $blockStudentIDs[$this->random(0, count($blockStudentIDs) - 1)];



        $conStudent = Student::select('id', 'national_id', 'dob')
            ->where('id', $conStudentID->student_id)
            ->first();

        $blockStudent = Student::select('id', 'national_id', 'dob')
            ->where('id', $blockStudentID->student_id)
            ->first();

        $data = [
            'Conventional student' => $conStudent,
            'Block student' => $blockStudent,
        ];

        return $this->sendData($data);
    }

    public function studentProfile($studentID)
    {
        // $student = Profile::where('student_id', $studentID)->first();
        $student = DB::table('students')
            ->join('profile', 'students.id', '=', 'profile.student_id')
            ->select([
                'students.fullName', 'students.gender',
                'profile.student_type', 'profile.part', 'profile.enrolled'
            ])
            ->where('students.id', $studentID)
            ->first();

        $data = [
            // 'studentNumber' => $studentID,
            'name' => $student->fullName,
            'gender' => $student->gender,
            'faculty' => $this->faculty($studentID),
            'program' => $this->program($studentID),
            'studentType' => $student->student_type,
            'part' => $student->part,
            'enrolled' => $student->enrolled,
        ];

        return $this->sendData($data);
    }

    protected function studentSpread()
    {
        $con = Profile::where('student_type', 'conventional')->get()->count();
        $block = Profile::where('student_type', 'block')->get()->count();


        $studentTypes = ['Conventional', 'Block'];

        $exclude = [];

        foreach ($studentTypes as $studentType) {
            $query = DB::table('profile')
                ->join('programmes', 'profile.program_id', '=', 'programmes.id')
                ->where('profile.student_type', $studentType)
                ->whereNot('programmes.faculty_id', 'COM')
                ->get()
                ->count();

            $exclude[] = $query;
        }


        $data = [
            'Conventional' => $con,
            'Block' => $block,
            'Excluding Commercials' => [
                'cons' => $exclude[0],
                'block' => $exclude[1],
            ]
        ];

        return $this->sendData($data);
    }
}
