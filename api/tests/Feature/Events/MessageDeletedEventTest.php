<?php

namespace Tests\Feature\Events;

use App\Events\MessageDeleted;
use App\Models\Conversation;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageDeletedEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_deleted_broadcasts_to_conversation_and_workspace_channels(): void
    {
        $workspace = Workspace::factory()->create();
        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);

        $event = new MessageDeleted('message-id-123', $conversation->id, $workspace->id, 'user-id-456');
        $channels = $event->broadcastOn();

        $this->assertCount(2, $channels);
        $this->assertEquals('private-conversation.' . $conversation->id, $channels[0]->name);
        $this->assertEquals('private-workspace.' . $workspace->id, $channels[1]->name);
    }

    public function test_message_deleted_includes_workspace_context(): void
    {
        $workspace = Workspace::factory()->create();
        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);

        $event = new MessageDeleted('message-id-123', $conversation->id, $workspace->id, 'user-id-456');
        $data = $event->broadcastWith();

        $this->assertEquals('message-id-123', $data['id']);
        $this->assertEquals($conversation->id, $data['conversation_id']);
        $this->assertEquals($workspace->id, $data['workspace_id']);
        $this->assertEquals('user-id-456', $data['deleted_by']);
    }
}
