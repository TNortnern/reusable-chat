<?php

namespace Tests\Unit\Services;

use App\Services\EmailGatewayService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EmailGatewayServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set test configuration
        Config::set('services.email_gateway.url', 'https://email-gateway-production.up.railway.app');
        Config::set('services.email_gateway.api_key', 'test_api_key');
    }

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
                   $request->hasHeader('X-API-Key', 'test_api_key') &&
                   $request['to'] === 'user@example.com' &&
                   $request['subject'] === 'Test Subject';
        });
    }

    public function test_handles_api_error_response(): void
    {
        Http::fake([
            'https://email-gateway-production.up.railway.app/send' => Http::response([
                'error' => 'Invalid recipient email',
            ], 400),
        ]);

        $service = new EmailGatewayService();
        $result = $service->send(
            to: 'invalid-email',
            subject: 'Test Subject',
            htmlBody: '<p>HTML body</p>',
            textBody: 'Text body'
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid recipient email', $result['error']);
    }

    public function test_handles_500_error(): void
    {
        Http::fake([
            'https://email-gateway-production.up.railway.app/send' => Http::response([
                'error' => 'Internal server error',
            ], 500),
        ]);

        $service = new EmailGatewayService();
        $result = $service->send(
            to: 'user@example.com',
            subject: 'Test Subject',
            htmlBody: '<p>HTML body</p>',
            textBody: 'Text body'
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('Internal server error', $result['error']);
    }

    public function test_handles_network_exception(): void
    {
        Http::fake(function () {
            throw new \Exception('Connection timeout');
        });

        $service = new EmailGatewayService();
        $result = $service->send(
            to: 'user@example.com',
            subject: 'Test Subject',
            htmlBody: '<p>HTML body</p>',
            textBody: 'Text body'
        );

        $this->assertFalse($result['success']);
        $this->assertEquals('Failed to send email. Please try again later.', $result['error']);
    }

    public function test_throws_exception_when_url_missing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Email gateway configuration missing');

        Config::set('services.email_gateway.url', null);

        new EmailGatewayService();
    }

    public function test_throws_exception_when_api_key_missing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Email gateway configuration missing');

        Config::set('services.email_gateway.api_key', null);

        new EmailGatewayService();
    }

    public function test_throws_exception_when_both_missing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Email gateway configuration missing');

        Config::set('services.email_gateway.url', null);
        Config::set('services.email_gateway.api_key', null);

        new EmailGatewayService();
    }
}
