<?php

namespace App\Traits;

use App\Models\Profile;
use App\Models\Student;
use App\Models\Tuition;
use App\Models\CheckInOut;
use App\Models\LoginTimestamps;
use App\Models\ActiveStudentType;
use Illuminate\Support\Facades\DB;


trait VUtils
{
    protected function getActiveStudentType()
    {
        $active = ActiveStudentType::select('student_type')
            ->where('active', 'Yes')->first();

        return $active->student_type;
    }


    protected function lowerLevel($currentLoopingLevel)
    {

        switch ($currentLoopingLevel) {
            case 4.2:
                return 4.1;
            case 4.1:
                return 4.2;
            case 3.2:
                return 3.1;
            case 3.1:
                return 3.2;
            case 2.2:
                return 2.1;
            case 2.1:
                return 2.2;
            case 1.2:
                return 1.1;
            case 1.1:
                return 1.2;
        }
    }
}
