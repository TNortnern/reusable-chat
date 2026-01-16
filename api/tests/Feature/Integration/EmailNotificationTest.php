<?php

namespace Tests\Feature\Integration;

use App\Events\MessageCreated;
use App\Jobs\SendMissedMessageEmail;
use App\Models\ChatUser;
use App\Models\Conversation;
use App\Models\EmailTemplate;
use App\Models\Message;
use App\Models\Workspace;
use App\Services\EmailGatewayService;
use App\Services\EmailTemplateRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_email_notification_flow(): void
    {
        Queue::fake();

        // Setup workspace with email notifications enabled
        $workspace = Workspace::factory()->create();
        $workspace->settings()->create([
            'workspace_id' => $workspace->id,
            'email_notifications' => [
                'enabled' => true,
                'triggers' => [
                    'missed_message_1on1_minutes' => 5,
                ],
            ],
            'email_branding' => [
                'from_name' => 'Test App',
                'reply_to' => 'support@test.com',
            ],
        ]);

        // Create email template
        EmailTemplate::factory()->create([
            'workspace_id' => $workspace->id,
            'event_type' => 'missed_message_1on1',
            'subject_template' => 'New message from {{sender.name}}',
            'body_html' => '<p>{{sender.name}} said: {{message.content}}</p>',
            'body_text' => '{{sender.name}} said: {{message.content}}',
        ]);

        // Create conversation with participants
        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);
        $sender = ChatUser::factory()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Alice',
            'email' => 'alice@test.com',
        ]);
        $recipient = ChatUser::factory()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Bob',
            'email' => 'bob@test.com',
        ]);

        $conversation->participants()->attach($sender->id, ['last_read_at' => now()]);
        $conversation->participants()->attach($recipient->id, ['last_read_at' => now()->subHour()]);

        // Send message
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => 'Hey Bob, how are you?',
        ]);

        // Trigger event
        event(new MessageCreated($message));

        // Assert job was dispatched with correct delay
        Queue::assertPushed(SendMissedMessageEmail::class, function ($job) use ($message) {
            return $job->message->id === $message->id &&
                   $job->delay !== null &&
                   abs($job->delay->diffInMinutes(now()->addMinutes(5))) < 1;
        });

        // Execute the job manually to test full flow
        $this->mock(EmailGatewayService::class)
            ->shouldReceive('send')
            ->once()
            ->withArgs(function ($to, $subject, $htmlBody, $textBody, $fromName, $replyTo) {
                return $to === 'bob@test.com' &&
                       $subject === 'New message from Alice' &&
                       str_contains($htmlBody, 'Alice said: Hey Bob, how are you?') &&
                       $fromName === 'Test App' &&
                       $replyTo === 'support@test.com';
            })
            ->andReturn(['success' => true, 'message_id' => 'test_123']);

        $job = new SendMissedMessageEmail($message);
        $job->handle(
            $this->app->make(EmailGatewayService::class),
            new EmailTemplateRenderer()
        );
    }
}
