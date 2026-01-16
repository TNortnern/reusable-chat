<?php

namespace Tests\Feature\Events;

use App\Events\ConversationCreated;
use App\Models\ChatUser;
use App\Models\Conversation;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationCreatedEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversation_created_broadcasts_to_workspace_channel(): void
    {
        $workspace = Workspace::factory()->create();
        $creator = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $conversation = Conversation::factory()->create([
            'workspace_id' => $workspace->id,
            'created_by' => $creator->id,
        ]);

        $event = new ConversationCreated($conversation);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertEquals('private-workspace.' . $workspace->id, $channels[0]->name);
    }

    public function test_conversation_created_includes_full_conversation_data(): void
    {
        $workspace = Workspace::factory()->create();
        $creator = ChatUser::factory()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
        ]);
        $conversation = Conversation::factory()->create([
            'workspace_id' => $workspace->id,
            'type' => 'group',
            'name' => 'Team Discussion',
            'created_by' => $creator->id,
        ]);

        // Add participants
        $participant2 = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $conversation->participants()->attach($creator->id, ['role' => 'admin']);
        $conversation->participants()->attach($participant2->id, ['role' => 'member']);

        $event = new ConversationCreated($conversation);
        $data = $event->broadcastWith();

        $this->assertEquals($conversation->id, $data['id']);
        $this->assertEquals('group', $data['type']);
        $this->assertEquals('Team Discussion', $data['name']);
        $this->assertEquals($workspace->id, $data['workspace_id']);
        $this->assertEquals($creator->id, $data['created_by']['id']);
        $this->assertEquals('Alice Smith', $data['created_by']['name']);
        $this->assertCount(2, $data['participants']);
    }
}
