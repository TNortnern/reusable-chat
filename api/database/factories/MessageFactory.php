<?php

namespace Database\Factories;

use App\Models\ChatUser;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_id' => ChatUser::factory(),
            'content' => $this->faker->paragraph(),
            'type' => 'text',
            'metadata' => [],
        ];
    }

    public function image(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'image',
            'content' => null,
        ]);
    }

    public function file(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'file',
            'content' => null,
        ]);
    }

    public function system(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'system',
            'sender_id' => null,
        ]);
    }
}
