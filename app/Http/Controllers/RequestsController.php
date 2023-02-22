<?php

namespace App\Http\Controllers;

use App\Traits\Utils;
use App\Traits\VUtils;
use App\Models\Student;
use App\Models\Requests;
use App\Models\Requester;
use App\Models\Residence;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Models\RequestCandidate;
use App\Http\Requests\CreateRequest;
use App\Models\SuburbRoom;

class RequestsController extends Controller
{
    //
    use Utils;
    use VUtils;
    use HttpResponses;

    private $activeStudentType = 'con';


    public function createRequest(CreateRequest $request)
    {
        $request->validated($request->all());

        /**
         * 1. Add the request initiator to requester table
         * 2. Add the request to the candidates table
         */

        try {
            //get requester gender
            $requesterGender = $this->gender($request->requester);

            //capture the requester
            Requester::create([
                'student_id' => $request->requester,
                'student_type' => $this->activeStudentType,
                'gender' => $requesterGender
            ]);

            //record the request on general requests
            Requests::create([
                'student_id' => $request->requester,
                'student_type' => $this->activeStudentType,
            ]);

            $candidates = [
                [
                    'requester_id' => $request->requester,
                    'selected_roomie' => $request->roomie1,
                    'student_type' => $this->activeStudentType,
                    'gender' => $requesterGender
                ],
                [
                    'requester_id' => $request->requester,
                    'selected_roomie' => $request->roomie2,
                    'student_type' => $this->activeStudentType,
                    'gender' => $requesterGender
                ],
                [
                    'requester_id' => $request->requester,
                    'selected_roomie' => $request->roomie3,
                    'student_type' => $this->activeStudentType,
                    'gender' => $requesterGender
                ],
            ];

            foreach ($candidates as $candidate) {
                //capture request candidates
                RequestCandidate::create($candidate);
            }

            //fetch the newly created request candidates
            $roomies = RequestCandidate::select(['selected_roomie', 'selection_confirmed'])
                ->where('requester_id', $request->requester)
                ->get();

            $data = [];

            foreach ($roomies as $roomie) {
                $student = Student::select('fullName')->where('id', $roomie->selected_roomie)->first();

                $push = [
                    "id" => $roomie->selected_roomie,
                    'fullName' => $student->fullName,
                    "response" => $roomie->selection_confirmed
                ];

                $data[] = $push;
            }

            return $this->sendData($data);
        } catch (\Exception $e) {
            echo get_class($e);

            return $this->sendData($e);
        }
    }

    //checking if the student has made a room request or has been selected as a roomie
    public function requestStatus($studentID)
    {
        $this->activeStudentType = $this->getActiveStudentType();

        //check if the portal is open for block students to 
        //request rooms
        $studentType = $this->studentType($studentID);

        if ($studentType === 'Block' && $this->activeStudentType === 'con')
            return $this->sendData(['status' => 'portalClosed']);

        // checking if the student has been allocated a room
        $hasRoom = Requests::where('student_id', $studentID)
            ->where('processed', 'Yes')->exists();


        if ($hasRoom) {
            //get the room 
            $res = Residence::select(['hostel', 'room'])->where('student_id', $studentID)->first();

            //get all the roomies in that room
            $roommates = Residence::select('student_id')
                ->where('room', $res->room)
                ->whereNot('student_id', $studentID)
                ->get();


            $roomies = [];

            //loop through over the roomies and add their names
            foreach ($roommates as $roomie) {
                $facade = [
                    "id" => $roomie->student_id,
                    'fullName' => $this->getFullName($roomie->student_id),
                ];

                $roomies[] = $facade;
            }

            $data = [
                'status' => 'allocated',
                'name' => Str::before($this->getFullName($studentID), ' '),
                'room' => $res->room,
                'roomies' => $roomies
            ];

            return $this->sendData($data);
        } else {
            //checking if rooms have been allocated for 
            //this particular student type
            if ($studentType === 'Conventional') $roomOccupationType = 'con_occupied';
            else $roomOccupationType = 'block_occupied';

            $roomsHaveBeenAllocated = SuburbRoom::where($roomOccupationType, 'Yes')->exists();

            if ($roomsHaveBeenAllocated)
                return $this->sendData(['status' => 'roomsAssigned']);

            // checking if the student has made a room request
            $requester = Requester::where('student_id', $studentID)->exists();

            switch ($requester) {
                case true:

                    $data = [
                        'status' => 'requester',
                        'roomies' => $this->pullPreferredMates($studentID)
                    ];

                    return $this->sendData($data);

                case false:
                    //check if the student has been selected as a rooomie
                    $selected = RequestCandidate::where('selected_roomie', $studentID)->exists();

                    if (!$selected) {
                        return $this->sendData(['status' => 'clean']);
                    }

                    //fetch the requester(s) that selected the roomie
                    $requester = RequestCandidate::select('requester_id')
                        ->where('selected_roomie', $studentID)
                        ->where('selection_confirmed', 'Waiting')
                        ->get();

                    //check how many requesters selected the student
                    //If they are more that one then it means the response of this
                    //student is neither 'yes' nor 'no' but 'waiting'

                    if (count($requester) > 1) {

                        $requesters = [];

                        foreach ($requester as $singleRequester) {
                            //fetch the roomies of this single requester
                            $roomies = $this->pullPreferredMates($singleRequester->requester_id, $studentID, 'selected');


                            //add the requester
                            $student = Student::select('fullName')->where('id', $singleRequester->requester_id)->first();
                            $requester = [
                                "id" => $singleRequester->requester_id,
                                'fullName' => $student->fullName,
                                "program" => $this->program($singleRequester->requester_id),
                                "gender" => $this->gender($singleRequester->requester_id)

                            ];

                            //prepare data
                            $data = [
                                'requester' => $requester,
                                'roomies' => $roomies
                            ];

                            $requesters[] = $data;
                        }

                        $data = [
                            'status' => 'waiting',
                            'type' => 'multi',
                            'requesters' => $requesters
                        ];

                        return $this->sendData($data);
                    } else {
                        // fetch the requester that selected the roomie
                        $requester = RequestCandidate::select('requester_id')
                            ->where('selected_roomie', $studentID)
                            ->first();

                        //fetch the other roomies
                        $roomies = $this->pullPreferredMates($requester->requester_id, $studentID, 'selected');

                        //check if the roomie has confirmed his/her selection request
                        $selectionResponse = RequestCandidate::select('selection_confirmed')
                            ->where('selected_roomie', $studentID)->first();

                        if ($selectionResponse->selection_confirmed === 'Yes') {
                            //append the requester to the list of roomies
                            $roomies[] = $this->pullRequesterData($requester->requester_id);

                            //prepare data
                            $data = [
                                'status' => 'confirmed',
                                'roomies' => $roomies
                            ];

                            return $this->sendData($data);
                        } elseif ($selectionResponse->selection_confirmed === 'No') {
                            //prepare data
                            $data = [
                                'status' => 'cancelled',
                            ];

                            return $this->sendData($data);
                        } elseif ($selectionResponse->selection_confirmed === 'Waiting') {
                            //add the requester
                            $student = Student::select('fullName')->where('id', $requester->requester_id)->first();
                            $requester = [
                                "id" => $requester->requester_id,
                                'fullName' => $student->fullName,
                                "program" => $this->program($requester->requester_id),
                                "gender" => $this->gender($requester->requester_id)

                            ];

                            //prepare data
                            $data = [
                                'status' => 'waiting',
                                'type' => 'single',
                                'requester' => $requester,
                                'roomies' => $roomies
                            ];


                            return $this->sendData($data);
                        }
                    }
            }
        }
    }

    public function roommateResponse(Request $request)
    {
        $this->activeStudentType = $this->getActiveStudentType();

        $request->validate([
            'studentID' => ['required', 'string', 'max:9', 'regex: /^L0\d{6}[A-Z]{1}$/u'],
            'requester' => ['string', 'max:9', 'regex: /^L0\d{6}[A-Z]{1}$/u'],
            'response' => ['required', 'string', 'min:2', 'max:3']
        ]);

        //studentID being the roomie who has sent a response

        switch ($request->response) {
            case 'yes':

                if ($request->requester) {
                    RequestCandidate::where('requester_id', $request->requester)
                        ->where('selected_roomie', $request->studentID)
                        ->update(['selection_confirmed' => $request->response]);

                    //nullify the other requester
                    RequestCandidate::whereNot('requester_id', $request->requester)
                        ->where('selected_roomie', $request->studentID)
                        ->update(['selection_confirmed' => 'No']);
                } else {
                    RequestCandidate::where('selected_roomie', $request->studentID)
                        ->update(['selection_confirmed' => $request->response]);
                }

                //add the student to the general requests
                Requests::create([
                    'student_id' => $request->studentID,
                    'student_type' => $this->activeStudentType,
                ]);

                //fetch the requester id
                $requester_id = $this->requester($request->studentID);

                //fetch the other roomies
                $roomies = $this->pullPreferredMates($requester_id, $request->studentID, 'selected');

                //append the requester to the list of roomies
                $roomies[] = $this->pullRequesterData($requester_id);

                return $this->sendData($roomies);
            case 'no':
                if ($request->requester) {
                    RequestCandidate::where('requester_id', $request->requester)
                        ->where('selected_roomie', $request->studentID)
                        ->update(['selection_confirmed' => $request->response]);
                } else {
                    RequestCandidate::where('selected_roomie', $request->studentID)
                        ->update(['selection_confirmed' => $request->response]);
                }

                return $this->sendResponse('Your consent has been updated');
            default:
                return $this->sendResponse('Invalid response');
        }
    }

    private function requester($selected_roomie)
    {
        //fetch the requester that selected the roomie
        $requester = RequestCandidate::select('requester_id')
            ->where('selected_roomie', $selected_roomie)->first();

        return $requester->requester_id;
    }

    private function pullRequesterData($requester_id)
    {
        //add the requester
        $student = Student::select('fullName')->where('id', $requester_id)->first();

        $requester = [
            "id" => $requester_id,
            'fullName' => $student->fullName,
            "program" => $this->program($requester_id),
            "gender" => $this->gender($requester_id),
            "response" => "Yes"

        ];

        return $requester;
    }

    public function revertResponse($studentID)
    {
        RequestCandidate::where('selected_roomie', $studentID)
            ->update(['selection_confirmed' => 'Waiting']);

        return $this->sendResponse('Your consent has been updated');
    }


    private function pullPreferredMates($requester, $selected_roomie = '', $type = 'requester')
    {

        switch ($type) {
            case 'requester':
                //fetch the newly created request candidates
                $roomies = RequestCandidate::select(['selected_roomie', 'selection_confirmed'])
                    ->where('requester_id', $requester)
                    ->get();
                break;
            case 'selected':
                //fetch the newly created request candidates
                $roomies = RequestCandidate::select(['selected_roomie', 'selection_confirmed'])
                    ->where('requester_id', $requester)
                    ->whereNot('selected_roomie', $selected_roomie)
                    ->get();
                break;
        }

        $data = [];

        foreach ($roomies as $roomie) {
            $student = Student::select('fullName')->where('id', $roomie->selected_roomie)->first();

            $push = [
                "id" => $roomie->selected_roomie,
                'fullName' => $student->fullName,
                "response" => $roomie->selection_confirmed,
                "program" => $this->program($roomie->selected_roomie)

            ];

            $data[] = $push;
        }

        return $data;
    }


    public function destroyRequest($studentID)
    {
        Requester::where('student_id', $studentID)->delete();
        RequestCandidate::where('requester_id', $studentID)->delete();
        Requests::where('student_id', $studentID)->delete();

        return $this->sendResponse('Delete successful');
    }
}
