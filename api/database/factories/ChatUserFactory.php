<?php

namespace Database\Factories;

use App\Models\ChatUser;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatUserFactory extends Factory
{
    protected $model = ChatUser::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'external_id' => $this->faker->uuid(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'avatar_url' => $this->faker->imageUrl(100, 100, 'people'),
            'metadata' => [],
            'is_anonymous' => false,
            'last_seen_at' => null,
        ];
    }

    public function anonymous(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_anonymous' => true,
            'name' => 'Anonymous User',
            'email' => null,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'last_seen_at' => now(),
        ]);
    }
}
