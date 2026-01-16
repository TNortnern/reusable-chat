<?php

namespace Tests\Unit\Services;

use App\Services\EmailGatewayService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmailGatewayServiceTest extends TestCase
{
    public function test_sends_email_via_gateway_api(): void
    {
        Http::fake([
            'https://email-gateway-production.up.railway.app/send' => Http::response([
                'success' => true,
                'message_id' => 'msg_123',
            ], 200),
        ]);

        $service = new EmailGatewayService();
        $result = $service->send(
            to: 'user@example.com',
            subject: 'Test Subject',
            htmlBody: '<p>HTML body</p>',
            textBody: 'Text body',
            fromName: 'Test App',
            replyTo: 'support@test.com'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('msg_123', $result['message_id']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://email-gateway-production.up.railway.app/send' &&
                   $request->hasHeader('X-API-Key', config('services.email_gateway.api_key')) &&
                   $request['to'] === 'user@example.com' &&
                   $request['subject'] === 'Test Subject';
        });
    }
}
