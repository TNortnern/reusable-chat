<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailGatewayService
{
    private string $url;
    private string $apiKey;

    public function __construct()
    {
        $url = config('services.email_gateway.url');
        $apiKey = config('services.email_gateway.api_key');

        if (empty($url) || empty($apiKey)) {
            throw new \RuntimeException('Email gateway configuration missing. Set EMAIL_GATEWAY_URL and EMAIL_GATEWAY_API_KEY.');
        }

        $this->url = $url;
        $this->apiKey = $apiKey;
    }

    /**
     * Send an email via the email gateway service.
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject line
     * @param string $htmlBody HTML version of email body
     * @param string $textBody Plain text version of email body
     * @param string $fromName Display name for sender
     * @param string|null $replyTo Optional reply-to email address
     * @return array{success: bool, message_id?: string, error?: string}
     */
    public function send(
        string $to,
        string $subject,
        string $htmlBody,
        string $textBody,
        string $fromName = 'Reusable Chat',
        ?string $replyTo = null
    ): array {
        try {
            $response = Http::timeout(10)
                ->retry(3, 100, throw: false)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                ])
                ->post($this->url . '/send', [
                    'to' => $to,
                    'subject' => $subject,
                    'html_body' => $htmlBody,
                    'text_body' => $textBody,
                    'from_name' => $fromName,
                    'reply_to' => $replyTo,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('message_id'),
                ];
            }

            Log::error('Email gateway error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('error', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('Email gateway exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to send email. Please try again later.',
            ];
        }
    }
}
