<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendMissedMessageEmail;
use App\Models\ChatUser;
use App\Models\Conversation;
use App\Models\EmailTemplate;
use App\Models\Message;
use App\Models\Workspace;
use App\Models\WorkspaceSettings;
use App\Services\EmailGatewayService;
use App\Services\EmailTemplateRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendMissedMessageEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_email_to_unread_participants(): void
    {
        $workspace = Workspace::factory()->create();

        WorkspaceSettings::factory()->create([
            'workspace_id' => $workspace->id,
            'email_notifications' => ['enabled' => true],
            'email_branding' => [
                'from_name' => 'Test App',
                'reply_to' => 'support@test.com',
            ],
        ]);

        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);
        $sender = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $recipient = ChatUser::factory()->create([
            'workspace_id' => $workspace->id,
            'email' => 'recipient@test.com',
        ]);

        // Add participants
        $conversation->participants()->attach($sender->id, ['last_read_at' => now()]);
        $conversation->participants()->attach($recipient->id, ['last_read_at' => now()->subHour()]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => 'Hello there',
        ]);

        EmailTemplate::factory()->create([
            'workspace_id' => $workspace->id,
            'event_type' => 'missed_message_1on1',
            'subject_template' => 'New message from {{sender.name}}',
            'body_html' => '<p>{{sender.name}} said: {{message.content}}</p>',
            'body_text' => '{{sender.name}} said: {{message.content}}',
        ]);

        $job = new SendMissedMessageEmail($message);

        $this->mock(EmailGatewayService::class)
            ->shouldReceive('send')
            ->once()
            ->withArgs(function ($to, $subject, $htmlBody, $textBody) use ($recipient, $sender) {
                return $to === 'recipient@test.com' &&
                       str_contains($subject, $sender->name) &&
                       str_contains($htmlBody, 'Hello there');
            })
            ->andReturn(['success' => true, 'message_id' => 'test_123']);

        $job->handle(
            $this->app->make(EmailGatewayService::class),
            new EmailTemplateRenderer()
        );
    }

    public function test_skips_sender_and_users_without_email(): void
    {
        $workspace = Workspace::factory()->create();

        WorkspaceSettings::factory()->create([
            'workspace_id' => $workspace->id,
            'email_notifications' => ['enabled' => true],
        ]);

        $conversation = Conversation::factory()->create(['workspace_id' => $workspace->id]);
        $sender = ChatUser::factory()->create(['workspace_id' => $workspace->id]);
        $noEmail = ChatUser::factory()->create([
            'workspace_id' => $workspace->id,
            'email' => null,
        ]);

        $conversation->participants()->attach($sender->id, ['last_read_at' => now()->subHour()]);
        $conversation->participants()->attach($noEmail->id, ['last_read_at' => now()->subHour()]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
        ]);

        EmailTemplate::factory()->create([
            'workspace_id' => $workspace->id,
            'event_type' => 'missed_message_1on1',
            'subject_template' => 'Test',
            'body_html' => '<p>Test</p>',
            'body_text' => 'Test',
        ]);

        $job = new SendMissedMessageEmail($message);

        $this->mock(EmailGatewayService::class)
            ->shouldReceive('send')
            ->never();

        $job->handle(
            $this->app->make(EmailGatewayService::class),
            new EmailTemplateRenderer()
        );
    }
}
