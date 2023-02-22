<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\Utils;
use App\Models\Profile;
use App\Models\Student;
use App\Models\Residence;
use App\Models\RoomRange;
use App\Models\CheckInOut;
use App\Models\HostelFees;
use Illuminate\Support\Str;
use App\Models\OldResidence;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Models\StudentTuition;
use App\Models\LoginTimestamps;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{

    use HttpResponses;
    use Utils;



    public function dashboardReminder($studentID)
    {
        $student = DB::table('students')
            ->join('payments', 'students.id', '=', 'payments.student_id')
            ->select(['students.fullName', 'payments.amount_cleared', 'payments.registered'])
            ->where('students.id', $studentID)
            ->first();

        $data = [
            'name' => Str::before($student->fullName, ' '),
            'tuition' => $this->facultyTuition($studentID),
            'amount_cleared' => $student->amount_cleared,
            'registered' => $student->registered
        ];

        return $this->sendData($data);
    }

    public function dashboardAside($studentID)
    {

        //get the accommodation fee
        $hostelFees = $this->hostelFees($studentID);
        //check-in-out dates
        $checkInOut = $this->checkInOut($studentID);
        //fetch the login time stamp
        $timestamp = LoginTimestamps::select('previous_stamp')->where('id', $studentID)->first();

        $previousTimeStamp = $timestamp->previous_stamp ? $timestamp->previous_stamp : null;

        $data = [
            "hostelFees" => $hostelFees,
            "checkIn" => $checkInOut['checkIn'],
            "checkOut" => $checkInOut['checkOut'],
            "timestamp" => $previousTimeStamp
        ];

        return $this->sendData($data);
    }

    public function profile($studentID)
    {
        $student = Profile::where('student_id', $studentID)->first();

        $data = [
            'studentNumber' => $student->student_id,
            'fullName' => $this->getFullName($studentID),
            'faculty' => $this->faculty($studentID),
            'program' => $this->program($studentID),
            'studentType' => $student->student_type,
            'part' => $student->part,
            'enrolled' => $student->enrolled,
            'timestamp' => $this->timestamp($studentID)
        ];

        return $this->sendData($data);
    }

    public function residence($studentID)
    {
        $currentRes = Residence::where('student_id', $studentID)->first();
        $checkInOut = $this->checkInOut($studentID);

        //if the student has not been allocated a room
        if (!$currentRes) {
            //check if the student has a previous residence record.
            $prevRes = OldResidence::where('student_id', $studentID)->first();

            //if the student doesnt have any record at all
            if (!$prevRes) {
                $data = [
                    "currentResidence" => false,
                    "hostel" => 'n/a',
                    "floor" => 'n/a',
                    "floorSide" => 'n/a',
                    "side" => 'n/a',
                    "room " => 'n/a',
                    "checkedIn"  => 'n/a',
                    "checkedOut"  => 'n/a',
                    "hostelFees" => $this->hostelFees($studentID),
                    "checkIn" => $checkInOut['checkIn'],
                    "checkOut" => $checkInOut['checkOut'],

                ];

                return $this->sendData($data);
            }


            return $this->resData($prevRes, $studentID, $checkInOut, false);
        } else {
            //if the student has been allocated a room
            return $this->resData($currentRes, $studentID, $checkInOut, true);
        }
    }


    private function resData($res, $studentID, $checkInOut, $resType)
    {

        if ($res->hostel === 'Suburb') {
            $resInfo = RoomRange::select('side', 'floor', 'suburb_floor_side')
                ->where('last_room', '>=', $res->room)->first();

            $side = $resInfo->side === 'F' ? 'Females' : 'Males';
            $floorSide = $resInfo->suburb_floor_side;
        } else {
            $resInfo = RoomRange::select('side', 'floor', 'mbundani_floor_side')
                ->where('last_room', '>=', $res->room)->first();

            $side = $resInfo->side === 'F' ? 'Females' : 'Males';
            $floorSide = $resInfo->mbundani_floor_side;
        }


        $data = [
            "currentResidence" => $resType,
            "hostel" => $res->hostel,
            "floor" => $resInfo->floor,
            "floorSide" => $floorSide,
            "side" => $side,
            "room" => $res->room,
            "part" => $res->part,
            "checkIn" => $checkInOut['checkIn'],
            "checkOut" => $checkInOut['checkOut'],
            "checkedIn"  => $res->checkedIn,
            "checkedOut"  => $res->checkedOut,
            "hostelFees" => $this->hostelFees($studentID),
        ];

        return $this->sendData($data);
    }

    //function to change password
    public function verifyCurrentPassword(UserRequest $request)
    {
        $request->validated($request->all());

        $student = User::select('password')->where('id', $request->studentID)->first();
        //if password is incorrect
        if (!Hash::check($request->password, $student->password)) return $this->sendError('Incorrect password', 401);

        return $this->sendResponse('Password verified successfully');
    }

    //function to change password
    public function updatePassword(UserRequest $request)
    {
        $request->validated($request->all());

        //update with new password
        User::where('id', $request->studentID)->update(['password' => Hash::make($request->password)]);

        return $this->sendResponse('Password updated successfully');
    }
}
