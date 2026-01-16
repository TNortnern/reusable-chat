<?php

namespace Tests\Unit\Models;

use App\Models\EmailTemplate;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_template_belongs_to_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $template = EmailTemplate::factory()->create(['workspace_id' => $workspace->id]);

        $this->assertInstanceOf(Workspace::class, $template->workspace);
        $this->assertEquals($workspace->id, $template->workspace->id);
    }

    public function test_email_template_casts_variables_to_array(): void
    {
        $template = EmailTemplate::factory()->create([
            'variables' => ['sender_name', 'unread_count'],
        ]);

        $this->assertIsArray($template->variables);
        $this->assertEquals(['sender_name', 'unread_count'], $template->variables);
    }
}
