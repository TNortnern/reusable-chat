<?php

namespace Tests\Feature\Events;

use App\Events\MessageCreated;
use App\Models\ChatUser;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageCreatedEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_message_created_broadcasts_to_conversation_and_workspace_channels(): void
    {
        $workspace = Workspace::factory()->create();
        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);
        $sender = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => 'Test message',
        ]);

        $event = new MessageCreated($message);
        $channels = $event->broadcastOn();

        $this->assertCount(2, $channels);
        $this->assertEquals('private-conversation.' . $conversation->id, $channels[0]->name);
        $this->assertEquals('private-workspace.' . $workspace->id, $channels[1]->name);
    }

    public function test_message_created_includes_rich_conversation_context(): void
    {
        $workspace = Workspace::factory()->create();
        $conversation = Conversation::factory()->create([
            'workspace_id' => $workspace->id,
            'type' => '1on1',
            'name' => 'Support Chat',
        ]);
        $sender = ChatUser::factory()->create([
            'workspace_id' => $workspace->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
        ]);

        $event = new MessageCreated($message);
        $data = $event->broadcastWith();

        $this->assertArrayHasKey('conversation', $data);
        $this->assertEquals($conversation->id, $data['conversation']['id']);
        $this->assertEquals('1on1', $data['conversation']['type']);
        $this->assertEquals('Support Chat', $data['conversation']['name']);
        $this->assertEquals($workspace->id, $data['conversation']['workspace_id']);
        $this->assertArrayHasKey('participant_count', $data['conversation']);
        $this->assertEquals($sender->email, $data['sender']['email']);
    }
}
