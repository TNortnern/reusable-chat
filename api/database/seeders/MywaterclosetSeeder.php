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

        // Ensure settings exist
        WorkspaceSettings::firstOrCreate(
            ['workspace_id' => $workspace->id],
            [
                'workspace_id' => $workspace->id,
                'welcome_message' => 'Welcome to MyWaterCloset! How can we help you today?',
                'auto_reply_enabled' => true,
                'auto_reply_message' => 'Thanks for reaching out! A team member will respond shortly.',
                'notification_email' => 'support@mywatercloset.com',
                'file_uploads_enabled' => true,
                'max_file_size_mb' => 10,
            ]
        );

        // Ensure theme exists with mywatercloset branding
        WorkspaceTheme::firstOrCreate(
            ['workspace_id' => $workspace->id],
            [
                'workspace_id' => $workspace->id,
                'primary_color' => '#0ea5e9',     // Sky blue
                'secondary_color' => '#0284c7',
                'background_color' => '#f0f9ff',
                'text_color' => '#0c4a6e',
                'border_radius' => 12,
                'font_family' => 'Inter, system-ui, sans-serif',
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
