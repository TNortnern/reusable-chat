<?php

namespace Database\Seeders;

use App\Models\Workspace;
use App\Models\WorkspaceSettings;
use App\Models\WorkspaceTheme;
use App\Models\ApiKey;
use Illuminate\Database\Seeder;

class WaterclosetSeeder extends Seeder
{
    public function run(): void
    {
        // API key for watercloset platform
        $apiKey = 'sk_wc_live_reusable_chat_watercloset_2026';

        // Create watercloset workspace
        $workspace = Workspace::firstOrCreate(
            ['slug' => 'watercloset'],
            [
                'name' => 'WaterCloset',
                'slug' => 'watercloset',
            ]
        );

        // Ensure settings exist
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

        // Ensure theme exists with watercloset branding
        WorkspaceTheme::firstOrCreate(
            ['workspace_id' => $workspace->id],
            [
                'workspace_id' => $workspace->id,
                'preset' => 'professional',
                'primary_color' => '#0f172a',     // Slate 900 - dark
                'background_color' => '#f8fafc',
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
                'name' => 'WaterCloset Production Key',
                'key_hash' => $keyHash,
                'key_prefix' => substr($apiKey, 0, 12) . '...',
            ]
        );

        $this->command->info('WaterCloset workspace created!');
        $this->command->info('Workspace ID: ' . $workspace->id);
        $this->command->info('API Key: ' . $apiKey);
    }
}
