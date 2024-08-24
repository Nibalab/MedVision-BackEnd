<?php

namespace Database\Factories;

use App\Models\CtScan;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition()
    {
        return [
            'ct_scan_id' => CtScan::factory(),
            'doctor_id' => User::factory()->create(['role' => 'doctor'])->id,
            'patient_id' => User::factory()->create(['role' => 'patient'])->id,
            'report_content' => $this->faker->paragraph,
            'status' => $this->faker->randomElement(['draft', 'finalized']),
        ];
    }
}

