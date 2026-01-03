<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WorkspaceFactory extends Factory
{
    protected $model = Workspace::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'id' => Str::uuid()->toString(),
            'name' => $name,
            'slug' => Str::slug($name),
            'plan' => 'free',
            'owner_id' => Admin::factory(),
        ];
    }

    public function withOwner(Admin $admin): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => $admin->id,
        ]);
    }
}
