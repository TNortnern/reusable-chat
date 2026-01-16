<?php

namespace App\Listeners;

use App\Events\MessageCreated;
use App\Jobs\SendMissedMessageEmail;

class DispatchMissedMessageEmail
{
    public function handle(MessageCreated $event): void
    {
        $message = $event->message;
        $workspace = $message->conversation->workspace;
        $settings = $workspace->settings;

        // Check if workspace has settings and email notifications are enabled
        if (!$settings || !($settings->email_notifications['enabled'] ?? false)) {
            return;
        }

        // Get delay from workspace settings
        $delayMinutes = $settings->email_notifications['triggers']['missed_message_1on1_minutes'] ?? 15;

        // Dispatch job with delay
        SendMissedMessageEmail::dispatch($message)
            ->delay(now()->addMinutes($delayMinutes));
    }
}
