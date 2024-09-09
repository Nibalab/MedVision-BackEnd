<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition()
    {
        $isSenderDoctor = $this->faker->boolean();
        $sender = $isSenderDoctor ? Doctor::factory()->create() : User::factory()->create(['role' => 'patient']);
        $receiver = !$isSenderDoctor ? Doctor::factory()->create() : User::factory()->create(['role' => 'patient']);

        return [
            'sender_id' => $sender->id,
            'sender_type' => get_class($sender),
            'receiver_id' => $receiver->id,
            'receiver_type' => get_class($receiver),
            'message_text' => $this->faker->sentence,
            'attachment' => $this->faker->optional()->filePath(),
            'read_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
            'is_read' => $this->faker->boolean(),
        ];
    }
}
