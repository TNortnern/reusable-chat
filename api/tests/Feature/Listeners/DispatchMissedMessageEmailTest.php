<?php

namespace Tests\Feature\Listeners;

use App\Events\MessageCreated;
use App\Jobs\SendMissedMessageEmail;
use App\Models\ChatUser;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Workspace;
use App\Models\WorkspaceSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DispatchMissedMessageEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatches_missed_message_email_job(): void
    {
        Queue::fake();

        $workspace = Workspace::factory()->create();
        $workspace->settings()->create([
            'workspace_id' => $workspace->id,
            'email_notifications' => [
                'enabled' => true,
                'triggers' => [
                    'missed_message_1on1_minutes' => 15,
                ],
            ],
        ]);

        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);
        $sender = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
        ]);

        event(new MessageCreated($message));

        Queue::assertPushed(SendMissedMessageEmail::class, function ($job) use ($message) {
            return $job->message->id === $message->id &&
                   $job->delay !== null &&
                   abs($job->delay->diffInMinutes(now()->addMinutes(15))) < 1;
        });
    }

    public function test_does_not_dispatch_when_email_notifications_disabled(): void
    {
        Queue::fake();

        $workspace = Workspace::factory()->create();
        $workspace->settings()->create([
            'workspace_id' => $workspace->id,
            'email_notifications' => ['enabled' => false],
        ]);

        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);
        $sender = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
        ]);

        event(new MessageCreated($message));

        Queue::assertNotPushed(SendMissedMessageEmail::class);
    }
}
