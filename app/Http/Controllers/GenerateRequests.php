<?php

namespace App\Http\Controllers;

use App\Traits\Utils;
use App\Models\Requests;
use App\Models\Requester;
use App\Models\SuburbRoom;
use App\Models\MbundaniRoom;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Models\RequestCandidate;
use App\Models\Residence;
use App\Traits\VUtils;
use Illuminate\Support\Facades\DB;

class GenerateRequests extends Controller
{


    /**
     * Game plan
     * 
     *  1. Fetch all the students of the same level and gender
     *  2. Loop through the students
     *  3. Check if the current student in loop has not made any requests
     *  4. Check if the current student is not part of any set of room mates
     *  5. If not make that student a requester
     *  6. Find the students room mates
     */

    use Utils;
    use VUtils;
    use HttpResponses;

    private $response = 3;
    private $results = [];
    private $currentLoopingRequester;
    private $activeStudentType = 'con';

    public function init()
    {
        $this->activeStudentType = $this->getActiveStudentType();

        $levels = [4.2, 4.1, 3.2, 3.1, 2.2, 2.1, 1.2];
        // $levels = [2.1];
        $genders = ['Female', 'Male'];
        // $genders = ['Male'];

        /*
         This loop based on gender will make sure we 
         allocate all the rooms to the females first
        */

        foreach ($genders as $gender) {
            foreach ($levels as  $level) {


                //Step 1
                /* 
                    Get the students on the same level with same gender & same student type
                */
                $pool = $this->genderFirstSameLevelStudents($gender, $level, $this->activeStudentType);


                // // if there is nothing look into another level
                if (!count($pool)) continue;

                //Step 2&3
                /* 
                    Out of the fetched students, get those that hv zero room requests
                    or have not been selected as a roomie 
                */
                $students = $this->freeMates($pool);

                // dd([
                //     'students' => $students,
                //     'level' => $level,
                //     'gender' => $gender
                // ]);

                // //lets create requests
                $this->createRequests($students, $gender);

                /* 
                    lets patch the create requests because some of
                    the selected roomies will decline and hence we need to 
                    create new requests for them
                */
            }
        }

        return $this->sendResponse('Done');

        // return $this->sendData($this->results);
    }

    private function createRequests($students, $gender)
    {
        $roommates = [];

        foreach ($students as $student) {
            //Step 4
            /*
                Make the current student in loop a requester
                but first check if the student is free
            */

            if (!$this->isNotFree($student)) {

                //lets get roommates for this student
                $roommates = $this->selectRoommates($student->id, $students, $gender);

                if (!$roommates) continue;
                else {
                    //Make the student a requester
                    Requester::create([
                        'student_id' => $student->id,
                        'student_type' => $this->activeStudentType,
                        'gender' => $gender
                    ]);

                    //Add the student to the general requests
                    Requests::create([
                        'student_id' => $student->id,
                        'student_type' => $this->activeStudentType,
                    ]);


                    foreach ($roommates as $roommate) {
                        //capture request candidates
                        RequestCandidate::create($roommate);
                        //add the student to the general requests if only
                        //they have a positive response - yes for selection-confirmed

                        if ($roommate['selection_confirmed'] === "Yes") {
                            Requests::create([
                                'student_id' => $roommate['selected_roomie'],
                                'student_type' => $this->activeStudentType,
                            ]);
                        }
                    }
                }
            }
        }

        return $roommates;
        // return $this->sendData($roommates);
    }

    private function selectRoommates($requester_id, $potentialRoomies, $gender)
    {
        //run the usual check
        $students = $this->freeMates($potentialRoomies);
        $roommates = [];

        foreach ($students as $student) {
            //check the number of free students
            if (count($this->freeMates($students)) < 3) {
                if (!$this->isNotFree($student) && $student->id !== $requester_id) {
                    $roommate = [
                        'requester_id' => $requester_id,
                        'selected_roomie' => $student->id,
                        'student_type' => $this->activeStudentType,
                        'selection_confirmed' => '',
                        'gender' => $gender
                    ];
                    $roommates[] = $roommate;
                }
            } else {
                if (count($roommates) === 3) break;

                if (!$this->isNotFree($student) && $student->id !== $requester_id) {
                    $roommate = [
                        'requester_id' => $requester_id,
                        'selected_roomie' => $student->id,
                        'student_type' => $this->activeStudentType,
                        'selection_confirmed' => '',
                        'gender' => $gender
                    ];
                    $roommates[] = $roommate;
                }
            }
        }


        return $this->setRoommateResponse($roommates);
    }

    private function setRoommateResponse($roomies)
    {
        $roommates = [];


        foreach ($roomies as $index => $roommate) {
            if ($this->response === 3) {
                $roommate['selection_confirmed'] = 'Yes';
            } else if ($this->response === 2) {
                if ($index === 2) $roommate['selection_confirmed'] = 'No';
                else $roommate['selection_confirmed'] = 'Yes';
            } else if ($this->response === 1) {
                if ($index === 0) $roommate['selection_confirmed'] = 'Yes';
                else $roommate['selection_confirmed'] = 'No';
            } else $roommate['selection_confirmed'] = 'No';


            $roommates[] = $roommate;
        }


        // if ($this->response === 1) $this->response = 0;
        // else $this->response = 0;

        if ($this->response === 3) $this->response = 2;
        elseif ($this->response === 2) $this->response = 1;
        elseif ($this->response === 1) $this->response = 0;
        else $this->response = 3;

        return $roommates;
    }

    private function isNotFree($student)
    {
        $exists = RequestCandidate::where('requester_id', $student->id)
            ->orWhere('selected_roomie', $student->id)->exists();

        if (!$exists) {
            $exists = Requester::where('student_id', $student->id)->exists();
        }

        return $exists;
    }


    private function freeMates($students)
    {

        //Check if the student exists as a requester
        //or selected roomie in the request_candidates table
        $freeMates = [];

        foreach ($students as $student) {
            $exists = RequestCandidate::where('requester_id', $student->id)
                ->orWhere('selected_roomie', $student->id)->exists();

            if (!$exists) {
                $freeMates[] = $student;
            } else {
                $declined = RequestCandidate::where('selected_roomie', $student->id)
                    ->where('selection_confirmed', 'No')
                    ->exists();

                if ($declined) $freeMates[] = $student;
                else continue;
            }
        }

        return $freeMates;
    }


    public function countGenderFirstSameLevelStudents($level)
    {

        $data = [
            [
                'gender' => 'Females',
                'number' => count($this->genderFirstLevelStudents('Female', $level)),
            ],            [
                'gender' => 'Males',
                'number' => count($this->genderFirstLevelStudents('Male', $level)),
            ]

        ];

        return $this->sendData($data);
    }

    private function genderFirstSameLevelStudents($gender, $level, $activeStudentType)
    {

        if ($activeStudentType === 'con') {
            $studentType = 'Conventional';
        } else {
            $studentType = 'Block';
        }

        $query = DB::table('students')
            ->join('profile', 'students.id', '=', 'profile.student_id')
            ->join('programmes', 'profile.program_id', '=', 'programmes.id')
            ->select('students.id')
            ->where('students.gender', $gender)
            ->where('profile.part', $level)
            ->where('profile.student_type', $studentType)
            ->whereNot('programmes.faculty_id', 'COM')
            ->get();


        if ($level === 3.1 || $level === 3.2) return $this->filterByProgram($query);
        else return $query;
    }

    private function filterByProgram($students)
    {
        $programExceptions = $this->programExceptions();
        $filteredStudents = [];

        foreach ($students as $student) {
            $studentProgramID = $this->programID($student->id);

            if (in_array($studentProgramID, $programExceptions)) {
                $filteredStudents[] = $student;
            }
        }

        return $filteredStudents;
    }

    public function stats()
    {
        $data = [
            'Processed' => Requests::where('student_type', $this->activeStudentType)
                ->where('processed', 'Yes')->count(),
            'Not processed' => Requests::where('student_type', $this->activeStudentType)
                ->where('processed', 'No')->count(),
            'Unprocessed students' => Requests::select('student_id')
                ->where('student_type', $this->activeStudentType)
                ->where('processed', 'No')->get(),
        ];


        return $this->sendData($data);
    }


    public function clearAll()
    {
        DB::statement("DELETE FROM students");
        DB::statement("DELETE FROM profile");
        DB::statement("DELETE FROM users");
        DB::statement("DELETE FROM user_login_timestamps");
        DB::statement("DELETE FROM payments");
        DB::statement("DELETE FROM residence");
        DB::statement("DELETE FROM old_residence");

        DB::statement("DELETE FROM requester");
        DB::statement("DELETE FROM requests");
        DB::statement("DELETE FROM request_candidates");

        DB::statement("UPDATE suburb_rooms SET con_occupied = 'No' ");
        DB::statement("UPDATE mbundani_rooms SET con_occupied = 'No' ");

        DB::statement("UPDATE suburb_rooms SET block_occupied = 'No' ");
        DB::statement("UPDATE mbundani_rooms SET block_occupied = 'No' ");

        return $this->sendResponse('Done.');
    }

    public function reverseProcessedRequests()
    {

        DB::statement("UPDATE requester SET processed = 'No' ");
        DB::statement("UPDATE requests SET processed = 'No' ");

        return $this->sendResponse('Reversing Done.');
    }

    public function clearRooms()
    {

        DB::statement("UPDATE suburb_rooms SET con_occupied = 'No' ");
        DB::statement("UPDATE mbundani_rooms SET con_occupied = 'No' ");

        DB::statement("UPDATE suburb_rooms SET block_occupied = 'No' ");
        DB::statement("UPDATE mbundani_rooms SET block_occupied = 'No' ");

        DB::statement("DELETE FROM residence");


        return $this->sendResponse('Rooms cleared');
    }


    public function destroyAll()
    {
        DB::statement('DELETE FROM requester');
        DB::statement('DELETE FROM requests');
        DB::statement('DELETE FROM request_candidates');

        return $this->sendResponse('Wipe done');
    }

    public function fakeRes()
    {
        $students = ["L0481923M", "L0492368S", "L0521648T"];
        $room = 351;
        $hostel = 'suburb';

        foreach ($students as $student) {
            Residence::create([
                'student_id' => $student,
                'part' => $this->part($student),
                'hostel' => $hostel,
                'room' => $room,
                // !!!! REMOVE THIS !!!!!!
                'roommates' => 3
            ]);
        }


        switch ($hostel) {
            case 'suburb':
                SuburbRoom::where('room_number', $room)
                    ->update(['occupied' => 'Yes']);


            case 'mbundani':
                MbundaniRoom::where('room_number', $room)
                    ->update(['occupied' => 'Yes']);
        }


        return $this->sendResponse("Done");
    }

    public function pullStudents()
    {
        $activeStudentType = 'con';

        $gender = 'Female';
        $level = 2.2;

        $pool = $this->genderFirstSameLevelStudents($gender, $level, $activeStudentType);

        $students = $this->freeMates($pool);


        return $this->sendData($students);
    }

    public function count()
    {

        $levels = [4.2, 4.1, 3.1, 3.2, 2.2, 2.1, 1.2];
        $genders = ['Female', 'Male'];

        // $levels = [2.2];
        // $genders = ['Female'];

        $wonderers = [];
        $males = [];
        $females = [];

        foreach ($genders as $gender) {
            foreach ($levels as $level) {
                $students = $this->genderFirstSameLevelStudents($gender, $level, $this->activeStudentType);

                // dd($students);

                if (!$students) continue;

                foreach ($students as $student) {

                    $studentExists = RequestCandidate::where('requester_id', $student->id)
                        ->orWhere('selected_roomie', $student->id)->exists();

                    if (!$studentExists) $wonderers[] = $student->id;
                }
            }
        }

        foreach ($wonderers as $student) {
            $studentInLoop = $this->studentProfile($student);

            if ($studentInLoop['gender'] === 'Female') $females[] = $studentInLoop;
            else $males[] = $studentInLoop;
        }

        $profiles = [
            'females' => $females,
            'males' => $males
        ];

        // return $this->sendData($wonderers);
        return $this->sendData($profiles);
    }
}
