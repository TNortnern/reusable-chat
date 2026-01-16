<?php

namespace Database\Factories;

use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'event_type' => 'missed_message_1on1',
            'subject_template' => 'You have a new message from {{sender_name}}',
            'body_html' => '<p>{{sender_name}} sent: {{content}}</p>',
            'body_text' => '{{sender_name}} sent: {{content}}',
            'variables' => ['sender_name', 'content'],
            'is_active' => true,
        ];
    }
}
