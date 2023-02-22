<?php

namespace App\Http\Controllers;

use App\Traits\Utils;
use App\Models\Requests;
use App\Models\Requester;
use App\Models\Residence;
use App\Models\SuburbRoom;
use App\Models\MbundaniRoom;
use App\Models\RequestCandidate;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Traits\VAudit;
use App\Traits\VUtils;
use Illuminate\Support\Facades\DB;

class VEngine extends Controller
{
    use HttpResponses;
    use Utils;
    use VAudit;
    use VUtils;

    /**
     * Game plan
     * 
     *  1. Fetch all requests(requesters) of the same level and gender
     *  2. Loop through the students
     *  3. Check if the current student in loop has not been given a room
     *  4. Get the room-mates of the student & check if they have not been given a room
     *  5. Check the number of confirmation the student has from his selected roommates
     *  6. Allocate a room
     */

    private $requesters;
    private $residence;

    private $levels = [4.2, 4.1, 3.2, 3.1, 2.2, 2.1, 1.2];
    private $genders = ['Female', 'Male'];

    private $activeStudentType = 'con';

    private $currentLoopingLevel;
    private $currentLoopingGender;
    private $currentLoopingRequester;



    public function init()
    {
        $this->activeStudentType = $this->getActiveStudentType();

        foreach ($this->genders as $gender) {
            foreach ($this->levels as $level) {
                //set these two globals
                $this->currentLoopingLevel = $level;
                $this->currentLoopingGender = $gender;

                //Step 1
                $requesters = $this->genderFirstSameLevelRequesters($gender, $level);

                //Step 3
                $this->requesters = $this->FreeRequesters($requesters);

                //lets begin the fun
                $this->processRequests($this->requesters, $gender);
            }
        }

        //run the init function of the VAudit
        $this->auditInit();

        return $this->sendResponse('Done');
    }

    public function audit()
    {
        return $this->auditInit();
    }

    private function processRequests($requesters, $gender)
    {

        foreach ($requesters as $requester) {
            //Set the global current looping requester state
            $this->currentLoopingRequester = $requester->student_id;

            //Step 4 - grab only the selected roomies that hv not been allocated a room 
            //and have a positive confirmation
            $selectedRoomies = $this->freeSelectedRoomies($requester->student_id);

            //Step 5
            //5.1.1 If all three roommates confirmed, straight away allocate them a room
            if (count($selectedRoomies) === 3) {
                $this->grantRoom($requester->student_id, $selectedRoomies, null);
            }

            //5.1.2 If two roommates confirmed, look for a roommate with zero confirmations
            elseif (count($selectedRoomies) === 2) {
                $orphanRequester = $this->set(0);
                if ($orphanRequester) {

                    /**
                     * 1. Delete all the selected roomies of the orphan requester
                     * 2. Add the orphaned requester as a selected roomie to the current looping requester
                     * 3. pull the newly created selected roomies
                     * 4. grant them a room
                     * 5. Update the requester processed status for the orphaned requester
                     */

                    //1
                    RequestCandidate::where('requester_id', $orphanRequester->student_id)->delete();

                    //2
                    RequestCandidate::create([
                        'requester_id' => $requester->student_id,
                        'selected_roomie' => $orphanRequester->student_id,
                        'student_type' => $this->activeStudentType,
                        'gender' => $this->currentLoopingGender,
                        'selection_confirmed' => 'Yes'
                    ]);

                    //3
                    $newlySelectedRoomies = $this->freeSelectedRoomies($requester->student_id);

                    //4&5 - Grant them a room
                    $this->grantRoom($requester->student_id, $newlySelectedRoomies, $orphanRequester->student_id);
                }
                //if we can't find an orphan requester
                else {
                    //Let's lookup for the number of sets where there are one confirmations
                    //if it returns true -> split otherwise look for a requester with one confirmation and split
                    $requestersWithOneConfirmation = $this->singleConfirmationRequesters(1);

                    // dd($requestersWithOneConfirmation);

                    if ($requestersWithOneConfirmation) {
                        $requesterWithOneConfirmation = $this->set(1);

                        if ($requesterWithOneConfirmation) {
                            //1. Grab the roomie who confirmed and leave the requester
                            //2. Make the roomie a selected roommate for the current looping requester
                            //3. Set all selected roomies response to No for the requestersWithOneConfirmation
                            //4. Grab the newly created selected roommates squad
                            //5. Allocate them a room


                            //1 This roomie is for the requesterWithOneConfirmation
                            $roomieWhoConfirmed = RequestCandidate::select('selected_roomie')
                                ->where('selection_confirmed', 'Yes')
                                ->where('requester_id', $requesterWithOneConfirmation->student_id)
                                ->first();

                            //2
                            RequestCandidate::create([
                                'requester_id' => $requester->student_id,
                                'selected_roomie' => $roomieWhoConfirmed->selected_roomie,
                                'student_type' => $this->activeStudentType,
                                'gender' => $this->currentLoopingGender,
                                'selection_confirmed' => 'Yes'
                            ]);

                            //3
                            RequestCandidate::where('requester_id', $requesterWithOneConfirmation->student_id)
                                ->update(['selection_confirmed' => 'No']);

                            //4
                            $newlySelectedRoomies = $this->freeSelectedRoomies($requester->student_id);

                            //5
                            $this->grantRoom(
                                $requester->student_id,
                                $newlySelectedRoomies,
                                null
                            );
                        }
                        //if we can't find a 4th roomie give the three roomies a room
                        else {
                            $this->grantRoom(
                                $requester->student_id,
                                $selectedRoomies,
                                null
                            );
                        }
                    }
                }
            }

            //5.1.3 If one roommate confirmed this makes two roomies, 
            // Option 1 -> find a requester with one confirmation
            // Option 2 -> find 2 requesters with zero confirmations
            elseif (count($selectedRoomies) === 1) {
                $requesterWithOneConfirmation = $this->set(1);
                // Option 1 -> find another squad of similar manner
                if ($requesterWithOneConfirmation) {
                    //1. grab that one roommate who confirmed for the requester with one confirmation
                    //2. add that roommate to the current looping requester roommates as a selected roomie
                    //3. add the requester with one confirmation as a roommate too
                    //4. grab the new selected roomies
                    //5. Grant them a room,
                    //6. Update requester processed status for the requesterWithOneConfirmation


                    //1 This roomie is for the requesterWithOneConfirmation
                    $roomieWhoConfirmed = RequestCandidate::select('selected_roomie')
                        ->where('selection_confirmed', 'Yes')
                        ->where('requester_id', $requesterWithOneConfirmation->student_id)
                        ->first();


                    //2
                    RequestCandidate::create([
                        'requester_id' => $requester->student_id,
                        'selected_roomie' => $roomieWhoConfirmed->selected_roomie,
                        'student_type' => $this->activeStudentType,
                        'gender' => $this->currentLoopingGender,
                        'selection_confirmed' => 'Yes'
                    ]);

                    //3
                    RequestCandidate::create([
                        'requester_id' => $requester->student_id,
                        'selected_roomie' => $requesterWithOneConfirmation->student_id,
                        'student_type' => $this->activeStudentType,
                        'gender' => $this->currentLoopingGender,
                        'selection_confirmed' => 'Yes'
                    ]);

                    //4 Get rid of all roomies of the requester with one confirmation
                    RequestCandidate::where('requester_id', $requesterWithOneConfirmation->student_id)
                        ->delete();

                    //5 Get rid of all the selected roomies of the current looping requester with response No
                    RequestCandidate::where('requester_id', $requester->student_id)
                        ->where('selection_confirmed', 'No')
                        ->delete();


                    //5 
                    $newlySelectedRoomies = $this->freeSelectedRoomies($requester->student_id);


                    //5&6
                    $this->grantRoom(
                        $requester->student_id,
                        $newlySelectedRoomies,
                        $requesterWithOneConfirmation->student_id
                    );
                }
                //Option 2 -> find 2 requesters with zero confirmations 
                else {

                    /*
                     1.1 Let's first count if we have enough requesters with zero confirmations.
                     - If we don't, let's look into another level deep
                    */
                    $requestersWithZeroConfirmations = $this->gatherRequesters();

                    // dd($requestersWithZeroConfirmations);

                    if (count($requestersWithZeroConfirmations) === 2) {
                        //add these requesters as roomies for the current looping requester
                        //and delete all selected roomies for these requesters

                        foreach ($requestersWithZeroConfirmations as $orphanRequester) {
                            RequestCandidate::create([
                                'requester_id' => $requester->student_id,
                                'selected_roomie' => $orphanRequester,
                                'student_type' => $this->activeStudentType,
                                'gender' => $this->currentLoopingGender,
                                'selection_confirmed' => 'Yes'
                            ]);

                            RequestCandidate::where('requester_id', $orphanRequester)->delete();
                        }

                        //delete all selected roomies of the current looping requester with no
                        //as the selection confirmation
                        RequestCandidate::where('requester_id', $requester->student_id)
                            ->where('selection_confirmed', 'No')
                            ->delete();


                        //grab the newly created requester
                        $newlySelectedRoomies = $this->freeSelectedRoomies($requester->student_id);

                        $this->grantRoom($requester->student_id, $newlySelectedRoomies, null);

                        //manually update the processed status of the requester with zero confirmations
                        foreach ($requestersWithZeroConfirmations as $orphanRequester) {
                            Requester::where('student_id', $orphanRequester)
                                ->update(['processed' => 'Yes']);
                        }
                    }
                }

                /* 
                    if nun of these conditions are met and the 
                    requester was not allocated a roomie after a full circle
                    the audit system should pick it up and make necessary adjustments
                */
            }
        }
    }


    private function grantRoom($requester_id, $selectedRoomies, $theOtherRequester_id)
    {


        if (!$this->freeRoom($this->currentLoopingGender)) {
            return $this->sendResponse('Rooms are full');
        };

        // //first update the requester table
        Requester::where('student_id', $requester_id)
            ->update(['processed' => 'Yes']);

        if ($theOtherRequester_id) {
            //Update the requester processed status for the other requester
            Requester::where('student_id', $theOtherRequester_id)
                ->update(['processed' => 'Yes']);
        }


        //update the rooms table
        if ($this->activeStudentType === 'con') $roomOccupationType = 'con_occupied';
        else $roomOccupationType = 'block_occupied';

        switch ($this->residence['hostel']) {
            case 'suburb':
                SuburbRoom::where('room', $this->residence['room_number'])
                    ->update([$roomOccupationType => 'Yes']);


            case 'mbundani':
                MbundaniRoom::where('room', $this->residence['room_number'])
                    ->update([$roomOccupationType  => 'Yes']);
        }

        $this->createResidence($requester_id, $selectedRoomies);
    }

    private function createResidence($requester_id, $selectedRoomies)
    {
        //migrate current residence data to old residence 

        //record residence for the requester
        Residence::create([
            'student_id' => $requester_id,
            'student_type' => $this->activeStudentType,
            'part' => $this->part($requester_id),
            'hostel' => $this->residence['hostel'],
            'room' => $this->residence['room_number'],
        ]);

        //record the residence for the roomies
        foreach ($selectedRoomies as $roomie) {

            Residence::create([
                'student_id' => $roomie->selected_roomie,
                'student_type' => $this->activeStudentType,
                'part' => $this->part($roomie->selected_roomie),
                'hostel' => $this->residence['hostel'],
                'room' => $this->residence['room_number'],
            ]);
        }
    }

    private function freeRoom($gender)
    {
        $hostels = ['suburb', 'mbundani'];

        switch ($gender) {
            case 'Female':

                foreach ($hostels as $hostel) {
                    if ($this->checkRoomAvailability($hostel, 331, 360)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'room_number' => $this->getRoom($hostel, 331, 360)
                        ];
                    } elseif ($this->checkRoomAvailability($hostel, 231, 260)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'room_number' => $this->getRoom($hostel, 231, 260)
                        ];
                    } elseif ($this->checkRoomAvailability($hostel, 216, 230)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'room_number' => $this->getRoom($hostel, 231, 260)
                        ];
                    } elseif ($this->checkRoomAvailability($hostel, 131, 160)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'room_number' => $this->getRoom($hostel, 131, 160)
                        ];
                    } elseif ($this->checkRoomAvailability($hostel, 101, 115)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'room_number' => $this->getRoom($hostel, 101, 115)
                        ];
                    }
                }
            case 'Male':

                foreach ($hostels as $hostel) {
                    if ($this->checkRoomAvailability($hostel, 301, 330)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'room_number' => $this->getRoom($hostel, 301, 330)
                        ];
                    } elseif ($this->checkRoomAvailability($hostel, 201, 215)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'room_number' => $this->getRoom($hostel, 201, 215)
                        ];
                    } elseif ($this->checkRoomAvailability($hostel, 116, 130)) {
                        return $this->residence = [
                            'hostel' => $hostel,
                            'room_number' => $this->getRoom($hostel, 116, 130)
                        ];
                    }
                }
        }
    }

    private function getRoom($hostel, $firstRoom, $lastRoom)
    {
        if ($this->activeStudentType === 'con') $roomOccupationType = 'con_occupied';
        else $roomOccupationType = 'block_occupied';

        switch ($hostel) {
            case 'suburb':
                $suburbRoom =  SuburbRoom::select('room')
                    ->where('usable', 'Yes')
                    ->where($roomOccupationType, 'No')
                    ->whereBetween('room', [$firstRoom, $lastRoom])
                    ->orderBy('room', 'desc')
                    ->first();

                return $suburbRoom->room;
            case 'mbundani':
                $mbundaniRoom = MbundaniRoom::select('room')
                    ->where('usable', 'Yes')
                    ->where($roomOccupationType, 'No')
                    ->whereBetween('room', [$firstRoom, $lastRoom])
                    ->orderBy('room', 'desc')
                    ->first();

                return $mbundaniRoom->room;
        }
    }


    private function checkRoomAvailability($hostel, $firstRoom, $lastRoom)
    {
        if ($this->activeStudentType === 'con') $roomOccupationType = 'con_occupied';
        else $roomOccupationType = 'block_occupied';

        switch ($hostel) {
            case 'suburb':
                $suburbRooms = SuburbRoom::whereBetween('room', [$firstRoom, $lastRoom])
                    ->where('usable', 'Yes')
                    ->where($roomOccupationType, 'No')
                    ->exists();

                return $suburbRooms;
            case 'mbundani':
                $mbundaniRooms = MbundaniRoom::whereBetween('room', [$firstRoom, $lastRoom])
                    ->where('usable', 'Yes')
                    ->where($roomOccupationType, 'No')
                    ->exists();

                return $mbundaniRooms;
        }
    }

    private function singleConfirmationRequesters($confirmations)
    {
        $singleConfirmationRequesters = [];

        foreach ($this->requesters as $requester) {
            $selectedRoomies = $this->freeSelectedRoomies($requester->student_id);

            if ($this->currentLoopingRequester === $requester->student_id) continue;
            else {

                if ($this->isFreeRequester($requester->student_id) && count($selectedRoomies) === $confirmations) {
                    //weed out any dublicates
                    if (in_array($requester->student_id, $singleConfirmationRequesters)) continue;
                    else {
                        $singleConfirmationRequesters[] = $requester->student_id;
                    }
                }
            }
        }

        //if the number of those requesters are even, this mean they can form 
        //a room, 2 + 2, otherwise if they are odd, which means one of the requester
        // cant form a room hence, we can grab one pair of 2 and split them and add
        //to the roomies short of 1.


        // dd($singleConfirmationRequesters);

        //if the array is empty there is nun to do, we return true so that if there
        //is a requester with two confirmations it can go through and get a room
        if (!$singleConfirmationRequesters) return true;
        if (count($singleConfirmationRequesters) === 1) return true;

        //return true that we can split them otherwise we cant split them
        return count($singleConfirmationRequesters) % 2 === 1 ? true : false;
    }

    private function gatherRequesters()
    {
        $confirmations = 0;
        $crowdRequesters = [];

        //We should get atleast one requester with zero confirmations

        foreach ($this->requesters as $requester) {
            $selectedRoomies = $this->freeSelectedRoomies($requester->student_id);

            if ($this->currentLoopingRequester === $requester->student_id) continue;
            else {
                if ($this->isFreeRequester($requester->student_id) && count($selectedRoomies) === $confirmations) {
                    if (in_array($requester->student_id, $crowdRequesters)) continue;
                    else {
                        $crowdRequesters[] = $requester->student_id;
                    }
                }
            }
        }

        // dd($crowdRequesters);

        //if we get one, look into another level and get one, that makes two

        if (count($crowdRequesters) === 1) {

            $lowerLevel = $this->lowerLevel($this->currentLoopingLevel);

            $lowerLevelRequesters =
                $this->genderFirstSameLevelRequesters($this->currentLoopingGender, $lowerLevel);

            $orphanRequester = $this->loopThroughRequesters($lowerLevelRequesters, $confirmations);

            if ($orphanRequester)  $crowdRequesters[] = $orphanRequester->student_id;
        }


        return $crowdRequesters;
    }


    /*
     This method gets, depending on the parameter
     1. A requester with zero roommate confirmation by default
     2. A requester with 1/2 roommate confirmation 
    */
    private function set($confirmations = 0)
    {
        //Loop through the requesters that match the current level & gender
        //and seek a requester who is FREE & has no confirmations from the selected roomies
        return $this->loopThroughRequesters($this->requesters, $confirmations);
    }

    private function loopThroughRequesters($requesters, $confirmations)
    {

        foreach ($requesters as $requester) {
            $selectedRoomies = $this->freeSelectedRoomies($requester->student_id);

            if ($this->currentLoopingRequester === $requester->student_id) continue;
            else {
                if ($this->isFreeRequester($requester->student_id) && count($selectedRoomies) === $confirmations) {
                    return  $requester;
                }
            }
        }
    }

    private function freeSelectedRoomies($requester_id)
    {
        //Get all the selected roomies of the current requester who confirmed
        $selectedRoomies = DB::table('request_candidates')
            ->select('selected_roomie')
            ->where('selection_confirmed', 'Yes')
            ->where('requester_id', $requester_id)
            ->get();

        return $this->freeMates($selectedRoomies);
    }

    private function freeMates($students)
    {
        $virginStudents = [];

        foreach ($students as $student) {
            $exists = Requester::where('student_id', $student->selected_roomie)
                ->where('processed', 'Yes')->exists();

            if ($exists) continue;
            else {
                $exists = Requests::where('student_id', $student->selected_roomie)
                    ->where('processed', 'Yes')
                    ->exists();

                if ($exists) continue;
                $virginStudents[] = $student;
            }
        }

        return $virginStudents;
    }

    private function isFreeRequester($requester_id)
    {
        // Basic rule - if the record exists then the requester is not free
        //return false
        $exists = Requester::where('student_id', $requester_id)
            ->where('processed', 'Yes')
            ->exists();

        if (!$exists) {
            $exists = Requests::where('student_id', $requester_id)
                ->where('processed', 'Yes')
                ->exists();

            if ($exists) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
    private function FreeRequesters($requesters)
    {
        $freeRequesters = [];

        foreach ($requesters as $requester) {
            $exists = Requests::where('student_id', $requester->student_id)
                ->where('processed', 'Yes')
                ->exists();

            if ($exists) continue;
            else $freeRequesters[] = $requester;
        }

        return $freeRequesters;
    }

    private function genderFirstSameLevelRequesters($gender, $level)
    {
        $query = DB::table('requester')
            ->join('profile', 'requester.student_id', '=', 'profile.student_id')
            ->select('requester.student_id')
            ->where('requester.gender', $gender)
            ->where('requester.processed', 'No')
            ->where('requester.student_type', $this->activeStudentType)
            ->where('profile.part', $level)
            ->get();

        return $query;
    }
}
