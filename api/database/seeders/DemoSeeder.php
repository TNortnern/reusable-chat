<?php

namespace Database\Seeders;

use App\Models\Workspace;
use App\Models\ApiKey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo workspace
        $workspace = Workspace::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Demo Workspace',
                'slug' => 'demo',
            ]
        );

        // Create demo API key
        ApiKey::firstOrCreate(
            ['key' => 'sk_demo_' . Str::random(32)],
            [
                'workspace_id' => $workspace->id,
                'name' => 'Demo API Key',
                'key' => 'sk_demo_reusable_chat_demo_key_2026',
            ]
        );

        $this->command->info('Demo workspace and API key created!');
        $this->command->info('API Key: sk_demo_reusable_chat_demo_key_2026');
    }
}
