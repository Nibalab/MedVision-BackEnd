<?php

namespace Database\Factories;

use App\Models\Annotation;
use App\Models\ThreeDModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnnotationFactory extends Factory
{
    protected $model = Annotation::class;

    public function definition()
    {
        return [
            'model_id' => ThreeDModel::factory(),
            'doctor_id' => User::factory()->create(['role' => 'doctor'])->id,
            'content' => $this->faker->sentence,
            'position' => $this->faker->randomElement(['top-left', 'top-right', 'bottom-left', 'bottom-right']),
        ];
    }
}

