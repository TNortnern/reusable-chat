<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Widget message sending: 30 per minute per user
        RateLimiter::for('widget-messages', function (Request $request) {
            $chatUser = $request->chatUser;
            $key = $chatUser ? 'chat-user:' . $chatUser->id : $request->ip();

            return Limit::perMinute(30)->by($key)->response(function () {
                return response()->json([
                    'error' => 'Too many messages. Please wait before sending another.',
                    'retry_after' => 60,
                ], 429);
            });
        });

        // Widget attachments: 10 per minute per user
        RateLimiter::for('widget-attachments', function (Request $request) {
            $chatUser = $request->chatUser;
            $key = $chatUser ? 'chat-user:' . $chatUser->id : $request->ip();

            return Limit::perMinute(10)->by($key)->response(function () {
                return response()->json([
                    'error' => 'Too many file uploads. Please wait before uploading another.',
                    'retry_after' => 60,
                ], 429);
            });
        });

        // Widget typing indicators: 60 per minute per user
        RateLimiter::for('widget-typing', function (Request $request) {
            $chatUser = $request->chatUser;
            $key = $chatUser ? 'chat-user:' . $chatUser->id : $request->ip();

            return Limit::perMinute(60)->by($key)->response(function () {
                return response()->json([
                    'error' => 'Too many typing requests.',
                    'retry_after' => 60,
                ], 429);
            });
        });

        // General widget rate limit for other endpoints
        RateLimiter::for('widget', function (Request $request) {
            $chatUser = $request->chatUser;
            $key = $chatUser ? 'chat-user:' . $chatUser->id : $request->ip();

            return Limit::perMinute(120)->by($key);
        });

        // API v1 endpoints: 100 per minute per API key
        RateLimiter::for('api-v1', function (Request $request) {
            // The workspace is set by ValidateApiKey middleware
            $workspace = $request->workspace;
            $key = $workspace ? 'workspace:' . $workspace->id : $request->ip();

            return Limit::perMinute(100)->by($key)->response(function () {
                return response()->json([
                    'error' => 'API rate limit exceeded. Please reduce your request frequency.',
                    'retry_after' => 60,
                ], 429);
            });
        });

        // Dashboard endpoints: 60 per minute per user
        RateLimiter::for('dashboard', function (Request $request) {
            $user = $request->user();
            $key = $user ? 'user:' . $user->id : $request->ip();

            return Limit::perMinute(60)->by($key);
        });

        // Login rate limiting: 5 attempts per minute (already used inline)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email') . '|' . $request->ip())->response(function () {
                return response()->json([
                    'error' => 'Too many login attempts. Please try again later.',
                    'retry_after' => 60,
                ], 429);
            });
        });

        // Embed API endpoints: 60 per minute per IP
        RateLimiter::for('embed', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip())->response(function () {
                return response()->json([
                    'error' => 'Rate limit exceeded. Please try again later.',
                    'retry_after' => 60,
                ], 429);
            });
        });
    }
}
