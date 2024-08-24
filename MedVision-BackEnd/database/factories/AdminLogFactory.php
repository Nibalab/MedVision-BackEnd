<?php

namespace Database\Factories;

use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdminLogFactory extends Factory
{
    protected $model = AdminLog::class;

    public function definition()
    {
        return [
            'admin_id' => User::factory()->create(['role' => 'admin'])->id,
            'action' => $this->faker->sentence,
        ];
    }
}
