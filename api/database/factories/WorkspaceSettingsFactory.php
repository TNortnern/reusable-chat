<?php

namespace Database\Factories;

use App\Models\Workspace;
use App\Models\WorkspaceSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkspaceSettingsFactory extends Factory
{
    protected $model = WorkspaceSettings::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'read_receipts_enabled' => true,
            'online_status_enabled' => true,
            'typing_indicators_enabled' => true,
            'file_size_limit_mb' => 10,
            'rate_limit_per_minute' => 30,
            'webhook_url' => null,
            'webhook_secret' => null,
        ];
    }

    public function withWebhook(): static
    {
        return $this->state(fn (array $attributes) => [
            'webhook_url' => fake()->url(),
            'webhook_secret' => fake()->sha256(),
        ]);
    }
}
