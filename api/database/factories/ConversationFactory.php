<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'type' => $this->faker->randomElement(['direct', 'group', 'support']),
            'name' => $this->faker->words(3, true),
            'created_by' => null,
        ];
    }

    public function direct(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'direct',
        ]);
    }

    public function group(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'group',
        ]);
    }

    public function support(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => 'support',
        ]);
    }
}
