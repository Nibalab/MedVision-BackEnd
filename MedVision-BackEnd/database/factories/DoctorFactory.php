<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), 
            'specialization' => $this->faker->randomElement(['Cardiology', 'Neurology', 'Orthopedics']),
            'bio' => $this->faker->paragraph,
            'contact_number' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
        ];
    }
}

