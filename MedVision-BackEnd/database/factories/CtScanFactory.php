<?php

namespace Database\Factories;

use App\Models\CtScan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CtScanFactory extends Factory
{
    protected $model = CtScan::class;

    public function definition()
    {
        return [
            'doctor_id' => User::factory()->create(['role' => 'doctor'])->id,
            'patient_id' => User::factory()->create(['role' => 'patient'])->id,
            'file_path' => $this->faker->filePath(),
        ];
    }
}

