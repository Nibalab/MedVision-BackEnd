<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // Default password for testing
            'role' => $this->faker->randomElement(['doctor', 'patient', 'admin']),
            'gender' => $this->faker->randomElement(['male', 'female', 'non-binary']),
            'profile_picture' => $this->faker->imageUrl(200, 200, 'people', true, 'Faker'),
            'remember_token' => Str::random(10),
        ];
    }
}
