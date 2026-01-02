<?php

namespace Database\Seeders;

use App\Models\Workspace;
use App\Models\ApiKey;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // The actual API key that clients will use
        $demoApiKey = 'sk_demo_reusable_chat_demo_key_2026';

        // Create demo workspace
        $workspace = Workspace::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Demo Workspace',
                'slug' => 'demo',
            ]
        );

        // Create demo API key (hashed for security)
        $keyHash = hash('sha256', $demoApiKey);

        ApiKey::firstOrCreate(
            ['key_hash' => $keyHash],
            [
                'workspace_id' => $workspace->id,
                'name' => 'Demo API Key',
                'key_hash' => $keyHash,
                'key_prefix' => substr($demoApiKey, 0, 12) . '...',
            ]
        );

        $this->command->info('Demo workspace and API key created!');
        $this->command->info('API Key: ' . $demoApiKey);
    }
}
