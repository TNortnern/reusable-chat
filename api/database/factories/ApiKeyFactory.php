<?php

namespace Database\Factories;

use App\Models\ApiKey;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ApiKeyFactory extends Factory
{
    protected $model = ApiKey::class;

    public function definition(): array
    {
        $plainKey = 'sk_live_' . Str::random(32);

        return [
            'workspace_id' => Workspace::factory(),
            'name' => fake()->words(3, true),
            'key_hash' => hash('sha256', $plainKey),
            'key_prefix' => substr($plainKey, 0, 12) . '...',
            'last_used_at' => null,
            'revoked_at' => null,
        ];
    }

    /**
     * Create an API key with a known plain key for testing
     */
    public function withKey(string $plainKey): static
    {
        return $this->state(fn (array $attributes) => [
            'key_hash' => hash('sha256', $plainKey),
            'key_prefix' => substr($plainKey, 0, 12) . '...',
        ]);
    }
}
