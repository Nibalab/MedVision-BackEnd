<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition()
    {
        return [
            'patient_id' => User::factory()->create(['role' => 'patient'])->id,
            'doctor_id' => User::factory()->create(['role' => 'doctor'])->id,
            'appointment_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'completed']),
        ];
    }
}

