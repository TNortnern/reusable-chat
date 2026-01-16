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
        $this->url = config('services.email_gateway.url');
        $this->apiKey = config('services.email_gateway.api_key');
    }

    public function send(
        string $to,
        string $subject,
        string $htmlBody,
        string $textBody,
        string $fromName = 'Reusable Chat',
        ?string $replyTo = null
    ): array {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->post($this->url . '/send', [
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
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
