<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Requests\ValidateUser;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\StudentNotFoundException;
use App\Exceptions\AccountExistsException;
use App\Models\LoginTimestamps;
use App\Traits\HttpResponses;
use App\Traits\Utils;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;

class AuthController extends Controller
{

    use HttpResponses;
    use Utils;

    public function validateCredentials(ValidateUser $request)
    {
        $request->validated($request->all());

        //verify student using studentID and nationalID
        $student =  Student::where('id', $request->studentID)->where('national_id', $request->nationalID)->first();

        if (!$student) {
            throw new StudentNotFoundException();
        }

        if ($request->dob) {
            //verify student with studentID and DOB
            $student = Student::where('id', $request->studentID)->where('dob', $request->dob)->first();

            if (!$student) {
                throw new StudentNotFoundException();
            }
        }

        return $this->sendResponse('Credentials verified successfully');
    }

    public function register(UserRequest $request)
    {
        $request->validated($request->all());

        //check if the student is not allowed to create an account
        //1. If you a commerce student boot out
        $studentFaculty = $this->facultyID($request->studentID);

        if ($studentFaculty === 'COM') {
            return $this->sendError('Unauthorized access!');
        }

        try {
            // create user 
            $user = User::create([
                'id' => $request->studentID,
                'password' => Hash::make($request->password)
            ]);


            // update the login in timestamp
            LoginTimestamps::create([
                'id' => $request->studentID,
                'current_stamp' => now(),
            ]);


            $userInfo = Student::select('fullName')->find($request->studentID);

            $data = [
                "studentNumber" => $request->studentID,
                "fullName" => $userInfo->fullName,
                "token" => $user->createToken('API token of ' . $userInfo->fullName)->plainTextToken
            ];

            return $this->sendData($data, 201);
        } catch (QueryException $e) {
            throw new AccountExistsException();
        } catch (\Exception $e) {
            echo get_class($e);
            return response()->json($e);
        }
    }

    public function login(UserRequest $request)
    {
        $request->validated($request->all());

        $verify = [
            'id' => $request->studentID,
            'password' => $request->password
        ];

        if (!Auth::attempt($verify)) {
            return $this->sendError('Invalid credentials', 401);
        }

        //check if the student is not allowed to login
        //1. If you a commerce student boot out
        //2. If you are a part 3 not in PRD boot out
        $studentFaculty = $this->facultyID($request->studentID);
        $studentPart = $this->part($request->studentID);
        $programExceptions = $this->programExceptions();
        $studentProgramID = $this->programID($request->studentID);

        if ($studentFaculty === 'COM') {
            return $this->sendError('Unauthorized access!');
        } elseif ($studentPart == 3.1 || $studentPart == 3.2) {
            if (!in_array($studentProgramID, $programExceptions))
                return $this->sendError('Unauthorized access!');
        }

        //get current login timestamp
        $timestamp = LoginTimestamps::select('current_stamp')->where('id', $request->studentID)->first();


        //update the timestamps
        LoginTimestamps::where('id', $request->studentID)->update([
            'current_stamp' => now(),
            'previous_stamp' => $timestamp->current_stamp
        ]);

        $user = Student::select('fullName')->find($request->studentID);

        //delete any existing tokens
        // Auth::user()->tokens()->delete();

        $data = [
            "studentNumber" => $request->studentID,
            "fullName" => $user->fullName,
            "token" => Auth::user()->createToken('API token of ' . $user->fullName)->plainTextToken
        ];

        return $this->sendData($data);
    }

    public function reset(UserRequest $request)
    {
        $request->validated($request->all());

        $user = User::where('id', $request->studentID)->update([
            'password' => Hash::make($request->password)
        ]);

        if (!$user) {
            throw new UserNotFoundException();;
        }

        $user = User::where('id', $request->studentID)->first();

        //delete any existing tokens
        $user->tokens()->delete();

        $userInfo = Student::select('fullName')->find($request->studentID);

        $data = [
            "studentNumber" => $request->studentID,
            "fullName" => $userInfo->fullName,
            "token" => $user->createToken('Api Token of ' . $userInfo->fullName)->plainTextToken
        ];

        return $this->sendData($data);
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
        return $this->sendResponse('Logged out');
    }

    public function destroy($studentID)
    {
        User::destroy($studentID);
        LoginTimestamps::destroy($studentID);

        return $this->sendResponse('User deleted successfully');
    }
}
