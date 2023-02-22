<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\LoginTimestamps;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $students = $this->fetchStudents();

        foreach ($students as $student) {
            User::create([
                'id' => $student->id,
                'password' => Hash::make('12345678')
            ]);

            // update the login in timestamp
            LoginTimestamps::create([
                'id' => $student->id,
                'current_stamp' => now(),
            ]);
        }
    }

    //students without accounts
    private function fetchStudents()
    {
        $students = Student::select('id')->get();
        $virgins = [];

        foreach ($students as $student) {
            $hasAccount = User::where('id', $student->id)->exists();

            if ($hasAccount) continue;
            else $virgins[] = $student;
        }

        return $virgins;
    }
}
