<?php

namespace Database\Factories;

use App\Models\CtScan;
use App\Models\ThreeDModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThreeDModelFactory extends Factory
{
    protected $model = ThreeDModel::class;

    public function definition()
    {
        return [
            'ct_scan_id' => CtScan::factory(),
            'model_path' => $this->faker->filePath(),
        ];
    }
}

