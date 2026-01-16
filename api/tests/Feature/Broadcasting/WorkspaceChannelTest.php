<?php

namespace Tests\Feature\Broadcasting;

use App\Models\Admin;
use App\Models\ChatUser;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceChannelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_subscribe_to_their_workspace_channel(): void
    {
        $workspace = Workspace::factory()->create();
        $admin = Admin::factory()->create();
        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'admin_id' => $admin->id,
            'role' => 'owner',
        ]);

        // Load the channel callback from routes/channels.php
        require base_path('routes/channels.php');

        // Get the channel callback
        $channels = app('Illuminate\Broadcasting\BroadcastManager')->getChannels();
        $callback = $channels['workspace.{workspaceId}'] ?? null;

        $this->assertNotNull($callback, 'Workspace channel is not registered');

        // Test authorization
        $result = $callback($admin, (string) $workspace->id);

        $this->assertTrue($result);
    }

    public function test_admin_cannot_subscribe_to_other_workspace_channel(): void
    {
        $workspace1 = Workspace::factory()->create();
        $workspace2 = Workspace::factory()->create();
        $admin = Admin::factory()->create();
        WorkspaceMember::create([
            'workspace_id' => $workspace1->id,
            'admin_id' => $admin->id,
            'role' => 'owner',
        ]);

        // Load the channel callback from routes/channels.php
        require base_path('routes/channels.php');

        // Get the channel callback
        $channels = app('Illuminate\Broadcasting\BroadcastManager')->getChannels();
        $callback = $channels['workspace.{workspaceId}'] ?? null;

        $this->assertNotNull($callback, 'Workspace channel is not registered');

        // Test authorization
        $result = $callback($admin, (string) $workspace2->id);

        $this->assertFalse($result);
    }

    public function test_chat_user_cannot_subscribe_to_workspace_channel(): void
    {
        $workspace = Workspace::factory()->create();
        $chatUser = ChatUser::factory()->create(['workspace_id' => $workspace->id]);

        // Load the channel callback from routes/channels.php
        require base_path('routes/channels.php');

        // Get the channel callback
        $channels = app('Illuminate\Broadcasting\BroadcastManager')->getChannels();
        $callback = $channels['workspace.{workspaceId}'] ?? null;

        $this->assertNotNull($callback, 'Workspace channel is not registered');

        // Test authorization
        $result = $callback($chatUser, (string) $workspace->id);

        $this->assertFalse($result);
    }
}
