<?php

namespace App\Http\Controllers;

use App\Traits\Utils;
use App\Models\Requester;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Models\RequestCandidate;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    use HttpResponses;
    use Utils;

    public function index(Request $request, $search_query)
    {
        $request->validate([
            'studentID' => ['required', 'string', 'max:9', 'regex: /^L0\d{6}[A-Z]{1}$/u']
        ]);

        //Step 1: Get the current student details
        $student = DB::table('students')
            ->join('profile', 'students.id', '=', 'profile.student_id')
            ->select('students.gender', 'profile.part')
            ->where('students.id', $request->studentID)
            ->first();


        //Step 2: query the students table with these restrictions

        //If the student is a part three and the program is in the exceptions 
        // then expand the search index to part 4's
        if ($student->part == 3.1 || $student->part == 3.2) {
            $programExceptions = $this->programExceptions();
            $studentProgramID = $this->programID($request->studentID);

            if (in_array($studentProgramID, $programExceptions)) {
                $searchResults = $this->specialSearch($request, $search_query, $student);
            }
        } else {
            $searchResults = $this->normalSearch($request, $search_query, $student);
        }



        //Step 3: differentiate the students that are already chosen by others from the rest
        $filteredResults = [];

        foreach ($searchResults as $student) {
            //check if the student has made a request
            $filter = RequestCandidate::where('requester_id', $student->id)
                ->orWhere('selected_roomie', $student->id)->exists();

            $facade = [
                'id' => $student->id,
                'fullName' => $student->fullName,
                'program' => $student->program,
            ];

            if ($filter) {

                $isRequester = RequestCandidate::where('requester_id', $student->id)->exists();

                switch ($isRequester) {
                    case true:
                        $facade['available'] = 'no';
                        break;
                    case false:
                        //check if the student has already confirmed yes:
                        $hasConfirmed = RequestCandidate::where('selection_confirmed', 'Yes')
                            ->where('selected_roomie', $student->id)->exists();

                        $facade['available'] = $hasConfirmed ? 'no' : 'yes';
                        break;
                }
            } else {
                $facade['available'] = 'yes';
            }

            $filteredResults[] = $facade;
        }


        //Step 3: send back the results
        return $this->sendData($filteredResults);
    }

    private function normalSearch($request, $search_query, $student)
    {
        $searchResults = DB::table('students')
            ->join('profile', 'students.id', '=', 'profile.student_id')
            ->join('programmes', 'profile.program_id', '=', 'programmes.id')
            ->select('students.id', 'students.fullName', 'programmes.program')
            ->where('students.id', 'LIKE', $search_query . '%')
            ->whereNot('students.id', $request->studentID)
            ->where('students.gender', $student->gender)
            ->whereNot('programmes.faculty_id', 'COM')
            ->where('profile.part', $student->part)
            ->get();

        return $searchResults;
    }

    private function specialSearch($request, $search_query, $student)
    {
        $programExceptions = $this->programExceptions();

        $searchResults = DB::table('students')
            ->join('profile', 'students.id', '=', 'profile.student_id')
            ->join('programmes', 'profile.program_id', '=', 'programmes.id')
            ->select('students.id', 'students.fullName', 'programmes.program')
            ->where('students.id', 'LIKE', $search_query . '%')
            ->whereNot('students.id', $request->studentID)
            ->where('students.gender', $student->gender)
            ->whereNot('programmes.faculty_id', 'COM')
            ->whereBetween('profile.part', [3.1, 4.2])
            ->get();

        $filteredResults = [];

        //remove all part 3's whose program is not in the exceptions
        foreach ($searchResults as $student) {
            $studentPart = $this->part($student->id);

            if ($studentPart == 3.1 || $studentPart == 3.2) {
                $studentProgramID = $this->programID($student->id);

                if (in_array($studentProgramID, $programExceptions)) {
                    $filteredResults[] = $student;
                }
            } else $filteredResults[] = $student;
        }

        return $filteredResults;
    }
}
