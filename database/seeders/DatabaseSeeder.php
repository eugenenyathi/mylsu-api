<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Database\Seeders\RoomsSeeder;
use Database\Seeders\ActiveStudentTypeSeeder;
use Database\Seeders\UsersSeeder;
use Database\Seeders\ProfileSeeder;
use Database\Seeders\ProgramSeeder;
use Database\Seeders\StudentSeeder;
use Database\Seeders\TuitionSeeder;
use Database\Seeders\PaymentsSeeder;
use Database\Seeders\FacultiesSeeder;
use Database\Seeders\ResidenceSeeder;
use Database\Seeders\RoomRangeSeeder;
use Database\Seeders\CheckInOutSeeder;
use Database\Seeders\HostelFeesSeeder;
use Database\Seeders\OldResidenceSeeder;
use Database\Seeders\SearchExceptionSeeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   *
   * @return void
   */
  public function run()
  {

    $this->call(FacultiesSeeder::class);
    $this->call(ProgramSeeder::class);
    $this->call(CheckInOutSeeder::class);
    $this->call(TuitionSeeder::class);
    $this->call(HostelFeesSeeder::class);

    $this->call(RoomsSeeder::class);
    $this->call(RoomRangeSeeder::class);

    $this->call(StudentSeeder::class);
    $this->call(ProfileSeeder::class);
    $this->call(UsersSeeder::class);
    $this->call(PaymentsSeeder::class);

    $this->call(OldResidenceSeeder::class);

    $this->call(SearchExceptionSeeder::class);
    $this->call(ActiveStudentTypeSeeder::class);
  }
}
