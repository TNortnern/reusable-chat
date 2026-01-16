<?php

namespace App\Jobs;

use App\Models\Message;
use App\Services\EmailGatewayService;
use App\Services\EmailTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMissedMessageEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Message $message
    ) {
        $this->message->load(['conversation.workspace.settings', 'sender']);
    }

    public function handle(
        EmailGatewayService $emailGateway,
        EmailTemplateRenderer $renderer
    ): void {
        $workspace = $this->message->conversation->workspace;
        $settings = $workspace->settings;

        // Check if email notifications are enabled
        $emailNotifications = $settings?->email_notifications ?? [];
        if (!($emailNotifications['enabled'] ?? false)) {
            return;
        }

        // Get email template
        $template = $workspace->emailTemplates()
            ->where('event_type', 'missed_message_1on1')
            ->where('is_active', true)
            ->first();

        if (!$template) {
            Log::warning('No email template found for missed_message_1on1', [
                'workspace_id' => $workspace->id,
            ]);
            return;
        }

        // Get recipients (participants who haven't read this message)
        $recipients = $this->message->conversation->participants()
            ->where('chat_user_id', '!=', $this->message->sender_id) // Exclude sender
            ->whereNotNull('chat_users.email') // Must have email
            ->where(function ($query) {
                $query->whereNull('participants.last_read_at')
                    ->orWhere('participants.last_read_at', '<', $this->message->created_at);
            })
            ->get();

        // Prepare template variables
        $variables = [
            'sender' => [
                'name' => $this->message->sender->name,
                'email' => $this->message->sender->email,
            ],
            'message' => [
                'content' => $this->message->content,
            ],
            'conversation' => [
                'name' => $this->message->conversation->name,
            ],
        ];

        // Send email to each recipient
        foreach ($recipients as $recipient) {
            $subject = $renderer->render($template->subject_template, $variables);
            $htmlBody = $renderer->render($template->body_html, $variables);
            $textBody = $renderer->render($template->body_text, $variables);

            $branding = $settings?->email_branding ?? [];

            $result = $emailGateway->send(
                to: $recipient->email,
                subject: $subject,
                htmlBody: $htmlBody,
                textBody: $textBody,
                fromName: $branding['from_name'] ?? 'Reusable Chat',
                replyTo: $branding['reply_to'] ?? null
            );

            if (!$result['success']) {
                Log::error('Failed to send missed message email', [
                    'recipient' => $recipient->email,
                    'message_id' => $this->message->id,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);
            }
        }
    }
}
