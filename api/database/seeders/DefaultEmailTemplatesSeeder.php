<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class DefaultEmailTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'event_type' => 'missed_message_1on1',
                'subject_template' => 'New message from {{sender.name}}',
                'body_html' => '<h2>You have a new message</h2><p><strong>{{sender.name}}</strong> said:</p><p>{{message.content}}</p>',
                'body_text' => '{{sender.name}} said: {{message.content}}',
                'variables' => ['sender.name', 'sender.email', 'message.content'],
            ],
            [
                'event_type' => 'missed_message_group',
                'subject_template' => '{{unread_count}} unread messages in {{conversation.name}}',
                'body_html' => '<h2>You have unread messages</h2><p>{{unread_count}} new messages in <strong>{{conversation.name}}</strong></p>',
                'body_text' => 'You have {{unread_count}} unread messages in {{conversation.name}}',
                'variables' => ['unread_count', 'conversation.name'],
            ],
            [
                'event_type' => 'new_participant',
                'subject_template' => '{{participant.name}} joined {{conversation.name}}',
                'body_html' => '<h2>New participant</h2><p><strong>{{participant.name}}</strong> joined {{conversation.name}}</p>',
                'body_text' => '{{participant.name}} joined {{conversation.name}}',
                'variables' => ['participant.name', 'conversation.name'],
            ],
            [
                'event_type' => 'new_inquiry',
                'subject_template' => 'New inquiry from {{sender.name}}',
                'body_html' => '<h2>New inquiry</h2><p><strong>{{sender.name}}</strong> started a conversation</p><p>{{message.content}}</p>',
                'body_text' => '{{sender.name}} started a conversation: {{message.content}}',
                'variables' => ['sender.name', 'message.content'],
            ],
        ];

        // Seed templates for all existing workspaces
        $workspaces = Workspace::all();

        foreach ($workspaces as $workspace) {
            foreach ($templates as $templateData) {
                EmailTemplate::updateOrCreate(
                    [
                        'workspace_id' => $workspace->id,
                        'event_type' => $templateData['event_type'],
                    ],
                    $templateData
                );
            }
        }
    }
}
