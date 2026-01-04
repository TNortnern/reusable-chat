<?php

namespace Database\Seeders;

use App\Models\Workspace;
use App\Models\WorkspaceSettings;
use App\Models\WorkspaceTheme;
use App\Models\ApiKey;
use Illuminate\Database\Seeder;

class MywaterclosetSeeder extends Seeder
{
    public function run(): void
    {
        // API key for mywatercloset production use
        $apiKey = 'sk_mwc_live_reusable_chat_mywatercloset_2026';

        // Create mywatercloset workspace
        $workspace = Workspace::firstOrCreate(
            ['slug' => 'mywatercloset'],
            [
                'name' => 'MyWaterCloset',
                'slug' => 'mywatercloset',
            ]
        );

        // Ensure settings exist (using actual schema columns)
        WorkspaceSettings::firstOrCreate(
            ['workspace_id' => $workspace->id],
            [
                'workspace_id' => $workspace->id,
                'read_receipts_enabled' => true,
                'online_status_enabled' => true,
                'typing_indicators_enabled' => true,
                'file_size_limit_mb' => 10,
                'rate_limit_per_minute' => 60,
            ]
        );

        // Ensure theme exists with mywatercloset branding (using actual schema columns)
        WorkspaceTheme::firstOrCreate(
            ['workspace_id' => $workspace->id],
            [
                'workspace_id' => $workspace->id,
                'preset' => 'professional',
                'primary_color' => '#0ea5e9',     // Sky blue
                'background_color' => '#f0f9ff',
                'font_family' => 'Inter, system-ui, sans-serif',
                'position' => 'bottom-right',
                'dark_mode_enabled' => true,
            ]
        );

        // Create API key (hashed for security)
        $keyHash = hash('sha256', $apiKey);

        ApiKey::firstOrCreate(
            ['key_hash' => $keyHash],
            [
                'workspace_id' => $workspace->id,
                'name' => 'MyWaterCloset Production Key',
                'key_hash' => $keyHash,
                'key_prefix' => substr($apiKey, 0, 12) . '...',
            ]
        );

        $this->command->info('MyWaterCloset workspace created!');
        $this->command->info('Workspace ID: ' . $workspace->id);
        $this->command->info('API Key: ' . $apiKey);
    }
}
