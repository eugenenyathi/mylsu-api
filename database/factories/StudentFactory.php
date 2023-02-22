<?php

namespace Database\Factories;

use App\Traits\FakeCredentials;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    use FakeCredentials;

    public function definition()
    {
        // $gender = $this->faker->randomElement(['Female', 'Male']);
        $gender = 'Female';

        return [
            'id' => $this->fakeStudentID(),
            'fullName' => $this->faker->name($gender),
            'dob' => $this->fakeDob(),
            'national_id' => $this->fakeNationalID(),
            'gender' => $gender
        ];
    }
}
