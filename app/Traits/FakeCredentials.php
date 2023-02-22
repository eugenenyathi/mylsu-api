<?php

namespace App\Traits;

use Illuminate\Support\Str;


trait FakeCredentials
{

    private $letters = "ABCDEFGHIJKLMNOQRSTUVWXYZ";
    private $numbers = "123456789";

    public function fakeNationalID()
    {
        /*
            1. Generate 9 random numbers 
            2. Append the random with a province code
            3. Swap the number on index 9 with a random letter
        */

        // Generate the random numbers
        $randomNumbers = str_shuffle($this->numbers);
        // Get 9 numbers
        $nineNumbers = Str::substr($randomNumbers, 0, 9);
        // Append with a province code
        $partialID = $this->addProvinceCode($nineNumbers);
        //Do step 3
        $nationalID = $this->replaceWithLetter($partialID);

        return $nationalID;
    }

    public function fakeStudentID()
    {
        /*
            1. Generate 6 random numbers 
            2. Append the L0 & add a random letter
        */

        $randomNumbers = str_shuffle($this->numbers);
        $sixNumbers = Str::substr($randomNumbers, 0, 6);
        $studentID = 'L0' . $sixNumbers . $this->letters[$this->random(0, Str::length($this->letters) - 1)];

        return $studentID;
    }

    private function fakeDob()
    {
        /*
            1. Generate day, month 
            2. Pick a year
        */
        $years = [1999, 2000, 2001, 2002, 2003];

        $day = $this->random(1, 31);
        $month = $this->random(1, 12);
        $year = $years[$this->random(0, 4)];

        if ($month === 2) {
            if ($year % 4 === 0) {
                $day = $this->random(1, 27);
            } else {
                $day = $this->random(1, 28);
            }
        } else if ($month === 4 || $month === 6 || $month === 7  || $month === 9 || $month === 11) {
            $day = $this->random(1, 28);
        }


        if ($day < 10) $day = '0' . $day;
        if ($month < 10) $month = '0' . $month;

        $dob =  $year . '-' . $month . '-' .  $day;

        return $dob;
    }

    private function random($firstIndex, $lastIndex)
    {
        return mt_rand($firstIndex, $lastIndex);
    }


    private function addProvinceCode($numbers)
    {
        $provinceCodes = ['79', '08', '06', '70'];
        $code = $provinceCodes[$this->random(0, 3)];

        return $code . '-' . $numbers;
    }

    //applies to generating a national ld
    private function replaceWithLetter($string)
    {
        $string[9] = $this->letters[$this->random(0, Str::length($this->letters) - 1)];

        return $string;
    }
}
