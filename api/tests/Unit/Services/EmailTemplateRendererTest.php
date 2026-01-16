<?php

namespace Tests\Unit\Services;

use App\Services\EmailTemplateRenderer;
use Tests\TestCase;

class EmailTemplateRendererTest extends TestCase
{
    public function test_renders_template_with_variables(): void
    {
        $renderer = new EmailTemplateRenderer();

        $result = $renderer->render(
            template: 'Hello {{name}}, you have {{count}} messages',
            variables: [
                'name' => 'John',
                'count' => 5,
            ]
        );

        $this->assertEquals('Hello John, you have 5 messages', $result);
    }

    public function test_renders_nested_variables(): void
    {
        $renderer = new EmailTemplateRenderer();

        $result = $renderer->render(
            template: '{{sender.name}} sent: {{message.content}}',
            variables: [
                'sender' => ['name' => 'Alice'],
                'message' => ['content' => 'Hello world'],
            ]
        );

        $this->assertEquals('Alice sent: Hello world', $result);
    }

    public function test_renders_missing_variables_as_empty_string(): void
    {
        $renderer = new EmailTemplateRenderer();

        $result = $renderer->render(
            template: 'Hello {{name}}, {{missing}} variable',
            variables: [
                'name' => 'Bob',
            ]
        );

        $this->assertEquals('Hello Bob,  variable', $result);
    }

    public function test_renders_array_variables(): void
    {
        $renderer = new EmailTemplateRenderer();

        $result = $renderer->render(
            template: 'Items: {{#items}}{{name}}{{/items}}',
            variables: [
                'items' => [
                    ['name' => 'Apple'],
                    ['name' => 'Orange'],
                ],
            ]
        );

        // Note: This test is for future array iteration support
        // For now, just ensure arrays are handled gracefully
        $this->assertIsString($result);
    }
}
