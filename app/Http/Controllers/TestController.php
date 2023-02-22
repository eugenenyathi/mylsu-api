<?php

namespace App\Http\Controllers;

use App\Traits\Utils;
use App\Models\Profile;
use App\Models\Student;
use App\Models\Tuition;
use App\Models\Requests;
use App\Models\Requester;
use App\Models\Residence;
use App\Models\RoomRange;
use App\Models\CheckInOut;
use App\Models\SuburbRoom;
use Illuminate\Support\Str;
use App\Models\MbundaniRoom;
use App\Models\OldResidence;
use App\Traits\ProgramsMall;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Models\SearchException;
use App\Traits\FakeCredentials;
use App\Models\RequestCandidate;
use App\Models\ActiveStudentType;
use App\Traits\VUtils;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    use HttpResponses;
    // use FakeCredentials;
    use Utils;
    use VUtils;
    use ProgramsMall;

    private $hostel;
    private $room;
    private $side;
    private $floor;
    private $floorSide;

    private $roomsWithLessStudents;
    private $roomsWithoutStudents;


    public function index()
    {
        $studentID = 'L0124876Q';

        $data =
            $this->studentType($studentID);

        return $this->sendData($data);
    }
}
