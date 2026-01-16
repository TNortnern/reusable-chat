<?php

namespace Tests\Feature\Integration;

use App\Events\ConversationCreated;
use App\Events\MessageCreated;
use App\Models\ApiKey;
use App\Models\ChatUser;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_workspace_monitoring_flow(): void
    {
        // Setup: Create workspace and API key
        $workspace = Workspace::factory()->create();
        $plainKey = 'sk_test_' . bin2hex(random_bytes(16));
        $apiKey = ApiKey::factory()
            ->withKey($plainKey)
            ->create(['workspace_id' => $workspace->id]);

        // Step 1: Get realtime token
        $tokenResponse = $this->postJson("/api/v1/workspaces/{$workspace->id}/realtime/token", [], [
            'X-API-Key' => $plainKey,
        ]);

        $tokenResponse->assertOk();
        $token = $tokenResponse->json('token');
        $this->assertNotEmpty($token);

        // Step 2: Create conversation and verify ConversationCreated event broadcasts to workspace channel
        $creator = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $conversation = Conversation::factory()->create([
            'workspace_id' => $workspace->id,
            'created_by' => $creator->id,
        ]);

        $conversationEvent = new ConversationCreated($conversation);
        $conversationChannels = $conversationEvent->broadcastOn();
        $conversationChannelNames = array_map(fn($ch) => $ch->name, $conversationChannels);

        // ConversationCreated only broadcasts to workspace channel
        $this->assertCount(1, $conversationChannels);
        $this->assertContains('private-workspace.' . $workspace->id, $conversationChannelNames);

        // Verify conversation event data includes workspace context
        $conversationData = $conversationEvent->broadcastWith();
        $this->assertArrayHasKey('workspace_id', $conversationData);
        $this->assertEquals($workspace->id, $conversationData['workspace_id']);

        // Step 3: Send message and verify MessageCreated event broadcasts to workspace channel
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $creator->id,
            'content' => 'Test message',
        ]);

        $messageEvent = new MessageCreated($message);
        $messageChannels = $messageEvent->broadcastOn();
        $messageChannelNames = array_map(fn($ch) => $ch->name, $messageChannels);

        $this->assertContains('private-conversation.' . $conversation->id, $messageChannelNames);
        $this->assertContains('private-workspace.' . $workspace->id, $messageChannelNames);

        // Verify message event data includes rich conversation and workspace context
        $messageData = $messageEvent->broadcastWith();
        $this->assertArrayHasKey('conversation', $messageData);
        $this->assertEquals($workspace->id, $messageData['conversation']['workspace_id']);
        $this->assertEquals($conversation->id, $messageData['conversation']['id']);
        $this->assertArrayHasKey('sender', $messageData);
        $this->assertEquals($creator->id, $messageData['sender']['id']);
    }
}
