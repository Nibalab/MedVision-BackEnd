<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition()
    {
        return [
            'sender_id' => User::factory()->create(['role' => 'doctor'])->id,
            'receiver_id' => User::factory()->create(['role' => 'patient'])->id,
            'message_text' => $this->faker->sentence,
            'attachment' => $this->faker->optional()->filePath(),
            'read_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
            'is_read' => $this->faker->boolean(), // Add is_read field with random true/false
        ];
    }
}
