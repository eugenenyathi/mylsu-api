<?php

namespace App\Traits;

use App\Models\Profile;
use App\Models\Student;
use App\Models\Tuition;
use App\Models\Requests;
use App\Models\Requester;
use App\Models\Residence;
use App\Models\CheckInOut;
use App\Models\SuburbRoom;
use App\Models\MbundaniRoom;
use App\Models\LoginTimestamps;
use Illuminate\Support\Facades\DB;


trait VAudit
{
    /*
    1. Get all requests from the general requests table
    2. Manually check if the student exists in the residence table
    3. If not look for a room with three students & is relevant in terms
    of gender and or level
    */
    use HttpResponses;
    use Utils;
    use VUtils;

    // private $parts = [4.2, 4.1, 3.2, 3.1, 2.2, 2.1, 1.2];
    private $currentLoopingStudent;
    private $residence;
    private $exists;
    private $roomsWithLessStudents;
    private $roomsWithoutStudents;

    private $activeStudentType = 'con';

    public function auditInit()
    {
        $this->activeStudentType = $this->getActiveStudentType();
        $studentsWithoutStudents = [];

        //1
        $requests = Requests::select('student_id')
            ->where('student_type', $this->activeStudentType)
            ->get();


        //2
        foreach ($requests as $request) {
            //check if the student has a room
            $hasRoom = Residence::where('student_id', $request->student_id)->exists();

            //if student has a room then pass
            //otherwise let's give them a room
            if ($hasRoom) {
                Requests::where('student_id', $request->student_id)
                    ->update(['processed' => 'Yes']);
            } else {
                // $studentsWithoutStudents[] = $request->student_id;

                $this->currentLoopingStudent = $request->student_id;
                //first get the gender of the student
                $gender = $this->gender($request->student_id);
                //3
                $this->processRooms($gender);
            }
        }


        return $this->sendData("Audit Done");
    }


    public function processRooms($gender)
    {
        //Check if all rooms in the current range
        //hv been occupied if not add otherwise also
        //check if those that are occupied are in full capacity
        //if not add to the list of rooms
        $this->rooms($gender);



        if ($this->roomsWithLessStudents) {
            //1.1 Get the room with the closest/exact level
            //with the current loop student
            $room = $this->matchRoomWithLevel($this->roomsWithLessStudents);

            $this->assignRoom($room);
        } elseif ($this->roomsWithoutStudents) {
            //grab the first room in the list
            $room = $this->roomsWithoutStudents[0];

            $this->assignRoom($room);
        } else {
            $data = [
                'error' => 'something went wrong with find free rooms',
                'currentLoopingStudent' => $this->currentLoopingStudent,
                'roomWithoutStudents' => $this->roomsWithoutStudents
            ];

            dd($data);
        }
    }

    private function assignRoom($room)
    {

        //check if the current looping student is a requester
        if ($this->isRequester($this->currentLoopingStudent)) {
            Requester::where('student_id', $this->currentLoopingStudent)
                ->update(['processed' => 'Yes']);
        }

        // //update the general requests table
        Requests::where('student_id', $this->currentLoopingStudent)
            ->update(['processed' => 'Yes']);


        //first verify if the room is already occupied,
        //if occupied don't update
        if ($this->activeStudentType === 'con') $roomOccupationType = 'con_occupied';
        else $roomOccupationType = 'block_occupied';

        switch ($this->residence['hostel']) {
            case 'suburb':
                $roomIsOccupied = SuburbRoom::select($roomOccupationType)->where('room', $room)->first();

                if ($roomIsOccupied->$roomOccupationType === 'Yes') break;
                else {
                    SuburbRoom::where('room', $room)
                        ->update([$roomOccupationType => 'Yes']);
                    break;
                }

            case 'mbundani':
                $roomIsOccupied = MbundaniRoom::select($roomOccupationType)->where('room', $room)->first();

                if ($roomIsOccupied->$roomOccupationType === 'Yes') break;
                else {
                    MbundaniRoom::where('room', $room)
                        ->update([$roomOccupationType => 'Yes']);
                    break;
                }
        }

        //add the student to the residence
        Residence::create([
            'student_id' => $this->currentLoopingStudent,
            'student_type' => $this->activeStudentType,
            'part' => $this->part($this->currentLoopingStudent),
            'hostel' => $this->residence['hostel'],
            'room' => $room,
        ]);

        // dd('Done');
    }

    private function isRequester($student_id)
    {
        $exists = Requester::where('student_id', $student_id)
            ->exists();

        return $exists;
    }

    private function matchRoomWithLevel($roomsWithLessStudents)
    {
        $currentLoopingLevel = $this->part($this->currentLoopingStudent);
        $closestStudentLevel = $this->lowerLevel($currentLoopingLevel);

        // Get the room with the closest/exact level
        // with the current loop student
        foreach ($roomsWithLessStudents as $room) {
            //get the first student attached to that room
            $firstStudent = Residence::select('student_id')->where('room', $room)->first();
            //now get this student's level/part
            $firstStudentLevel = $this->part($firstStudent->student_id);

            //now compare this level with the current looping level
            //if it qualifies return the room 
            if ($firstStudentLevel === $currentLoopingLevel || $firstStudentLevel === $closestStudentLevel) {
                return $room;
            }
        }

        //otherwise return the first room that comes
        return $roomsWithLessStudents[0];
    }

    private function rooms($gender)
    {
        $hostels = ['suburb', 'mbundani'];

        switch ($gender) {
            case 'Female':
                foreach ($hostels as $hostel) {
                    if ($this->roomExists($hostel, 331, 360)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'rooms' => $this->roomExists($hostel, 331, 360)
                        ];
                    } elseif ($this->roomExists($hostel, 231, 260)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'rooms' => $this->roomExists($hostel, 231, 260)
                        ];
                    } elseif ($this->roomExists($hostel, 216, 230)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'rooms' => $this->roomExists($hostel, 216, 230)
                        ];
                    } elseif ($this->roomExists($hostel, 131, 160)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'rooms' => $this->roomExists($hostel, 131, 160)
                        ];
                    } elseif ($this->roomExists($hostel, 101, 115)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'rooms' => $this->roomExists($hostel, 101, 115)
                        ];
                    }
                }
                break;
            case 'Male':

                foreach ($hostels as $hostel) {
                    if ($this->roomExists($hostel, 301, 330)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'rooms' => $this->roomExists($hostel, 301, 330)
                        ];
                    } elseif ($this->roomExists($hostel, 201, 215)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'rooms' => $this->roomExists($hostel, 201, 215)
                        ];
                    } elseif ($this->roomExists($hostel, 116, 130)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'rooms' => $this->roomExists($hostel, 116, 130)
                        ];
                    }
                }
                break;
        }
    }

    private function roomExists($hostel, $firstRoom, $lastRoom)
    {
        $this->roomsWithLessStudents = [];
        $this->roomsWithoutStudents = [];

        $rooms = [];

        for ($room = $lastRoom; $room >= $firstRoom; $room--) {
            $exists = Residence::where('hostel', $hostel)
                ->where('room', $room)
                ->where('student_type', $this->activeStudentType)
                ->exists();

            if ($exists) {
                $this->exists[] = $room;

                if ($this->checkRoomStudentCount($hostel, $room)) {
                    $this->roomsWithLessStudents[] = $room;
                    $rooms[] = $room;
                }
            } else {
                $this->roomsWithoutStudents[] = $room;
                $rooms[] = $room;
            }
        }

        return $rooms;
    }

    private function checkRoomStudentCount($hostel, $room_number)
    {
        $query = Residence::select(DB::raw('room, count(room) as student_count'))
            ->where('hostel', $hostel)
            ->where('room', $room_number)
            ->where('student_type', $this->activeStudentType)
            ->groupBy('room')
            ->first();

        if ($query->student_count < 4) return true;
        else false;
    }
}
