<?php

namespace App\Traits;

use App\Models\Profile;
use App\Models\Tuition;
use App\Models\CheckInOut;
use App\Models\LoginTimestamps;
use App\Models\SearchException;
use App\Models\Student;
use Illuminate\Support\Facades\DB;


trait Utils
{


    protected function faculty($studentID)
    {
        $faculty = DB::table('profile')
            ->join('programmes', 'profile.program_id', '=', 'programmes.id')
            ->join('faculties', 'programmes.faculty_id', '=', 'faculties.id')
            ->select('faculties.faculty')
            ->where('student_id', $studentID)
            ->first();


        return $faculty->faculty;
    }

    protected function facultyID($studentID)
    {
        $faculty = DB::table('profile')
            ->join('programmes', 'profile.program_id', '=', 'programmes.id')
            ->select('programmes.faculty_id')
            ->where('student_id', $studentID)
            ->first();


        return $faculty->faculty_id;
    }

    protected function facultyTuition($studentID)
    {
        //get what type of student is it
        $studentType = $this->studentType($studentID);

        if ($studentType === 'Conventional') $tuitionColumn = 'con_amount';
        else $tuitionColumn = 'block_amount';

        $tuition = DB::table('profile')
            ->join('programmes', 'profile.program_id', '=', 'programmes.id')
            ->join('tuition', 'programmes.faculty_id', '=', 'tuition.faculty_id')
            ->select($tuitionColumn)
            ->where('profile.student_id', $studentID)
            ->first();

        return $tuition->$tuitionColumn;
        // return $tuition;
    }

    protected function program($studentID)
    {
        $program = DB::table('profile')
            ->join('programmes', 'profile.program_id', '=', 'programmes.id')
            ->select('programmes.program')
            ->where('profile.student_id', $studentID)
            ->first();

        return $program->program;
    }

    protected function programID($studentID)
    {
        $program = Profile::select('program_id')->where('student_id', $studentID)->first();

        return $program->program_id;
    }

    protected function programExceptions()
    {
        $programs = SearchException::all();
        $data = [];

        foreach ($programs as $program) {
            $data[] = $program->program_id;
        }

        return $data;
    }


    protected function studentType($studentID)
    {
        $profile = Profile::select('student_type')->where('student_id', $studentID)->first();

        return $profile->student_type;
    }

    protected function timestamp($studentID)
    {
        //get current login timestamp
        $timestamp = LoginTimestamps::select('current_stamp')
            ->where('id', $studentID)->first();

        return $timestamp->current_stamp;
    }


    public function hostelFees($studentID)
    {
        $hostel = DB::table('profile')
            ->join('hostel_fees', 'profile.student_type', '=', 'hostel_fees.student_type')
            ->select('hostel_fees.fee')
            ->where('profile.student_id', $studentID)
            ->first();

        return $hostel->fee;
    }


    public function checkInOut($studentID)
    {
        //get the type of a student
        $studentType = $this->studentType($studentID);

        if ($studentType === 'Conventional') $dateColumn = 'con_students_date';
        else $dateColumn = 'block_students_date';

        $checkInOut = CheckInOut::select('type', 'con_students_date', 'block_students_date')->get();

        $data = [
            'checkIn' => $checkInOut[0]->$dateColumn,
            'checkOut' => $checkInOut[1]->$dateColumn,
        ];

        return $data;
    }

    public function part($studentID)
    {
        $profile = Profile::select('part')->where('student_id', $studentID)->first();

        return $profile->part;
    }


    public function previousPart($studentID)
    {
        $levels = [1.1, 1.2, 2.1, 2.2, 4.1, 4.2];
        //fetch student level
        $student = Profile::select('part')->where('student_id', $studentID)->first();
        $indexOfCurrentLevel = array_search($student->part, $levels);
        $previousLevel = $levels[$indexOfCurrentLevel - 1];

        return $previousLevel;
    }

    public function gender($studentID)
    {
        $student = Student::select('gender')->where('id', $studentID)->first();
        return $student->gender;
    }

    public function genderFirstLevelStudents($gender, $level)
    {
        $query = DB::table('students')
            ->join('profile', 'students.id', '=', 'profile.student_id')
            ->join('programmes', 'profile.program_id', '=', 'programmes.id')
            ->select('students.id')
            ->where('students.gender', $gender)
            ->where('profile.part', $level)
            ->whereNot('programmes.faculty_id', 'COM')
            ->get();

        return $query;
    }

    protected function random($firstIndex, $lastIndex)
    {
        return mt_rand($firstIndex, $lastIndex);
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
            'studentNumber' => $studentID,
            'name' => $student->fullName,
            'gender' => $student->gender,
            'faculty' => $this->faculty($studentID),
            'program' => $this->program($studentID),
            'studentType' => $student->student_type,
            'part' => $student->part,
            'enrolled' => $student->enrolled,
        ];

        // return $this->sendData($data);
        return $data;
    }

    public function getFullName($studentID)
    {
        $student = Student::select('fullName')->where('id', $studentID)->first();

        return $student->fullName;
    }
}
