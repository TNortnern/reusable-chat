<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\V1\SessionController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\ModerationController;
use App\Http\Controllers\Api\V1\DemoController;
use App\Http\Controllers\Widget\MeController;
use App\Http\Controllers\Widget\ConversationController as WidgetConversationController;
use App\Http\Controllers\Widget\MessageController;
use App\Http\Controllers\Widget\ReactionController;
use App\Http\Controllers\Widget\ReadReceiptController;
use App\Http\Controllers\Widget\TypingController;
use App\Http\Controllers\Widget\AttachmentController;
use App\Http\Controllers\Widget\BlockController;
use App\Http\Controllers\Widget\BroadcastAuthController;
use App\Http\Controllers\Dashboard\AuthController;
use App\Http\Controllers\Dashboard\WorkspaceController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\ThemeController;
use App\Http\Controllers\Dashboard\ApiKeyController;
use App\Http\Controllers\Dashboard\ConversationController as DashboardConversationController;
use App\Http\Controllers\Dashboard\UserController as DashboardUserController;
use App\Http\Controllers\Dashboard\AnalyticsController;

// Consumer Backend API (v1) - API Key Auth
Route::prefix('v1')->middleware(['api.key', 'throttle:api-v1'])->group(function () {
    // Sessions
    Route::post('/sessions', [SessionController::class, 'store']);
    Route::delete('/sessions/{id}', [SessionController::class, 'destroy']);

    // Users
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{external_id}', [UserController::class, 'show']);

    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations/unread', [ConversationController::class, 'unreadCount']);
    Route::post('/conversations/{id}/participants', [ConversationController::class, 'addParticipant']);

    // Moderation
    Route::post('/users/{id}/ban', [ModerationController::class, 'ban']);
    Route::delete('/users/{id}/ban', [ModerationController::class, 'unban']);
    Route::get('/bans', [ModerationController::class, 'index']);

    // Demo
    Route::post('/demo/rooms', [DemoController::class, 'createRoom']);
});

// Widget API - Session Token Auth
Route::prefix('widget')->middleware(['session.token', 'throttle:widget'])->group(function () {
    Route::get('/me', [MeController::class, 'show']);

    // Broadcasting auth for Reverb
    Route::post('/broadcasting/auth', [BroadcastAuthController::class, 'authenticate']);

    Route::get('/conversations', [WidgetConversationController::class, 'index']);
    Route::post('/conversations', [WidgetConversationController::class, 'store']);
    Route::get('/conversations/{id}', [WidgetConversationController::class, 'show']);

    // Messages - specific rate limit + content validation
    Route::post('/conversations/{conversationId}/messages', [MessageController::class, 'store'])
        ->middleware(['throttle:widget-messages', 'validate.message']);

    Route::post('/conversations/{conversationId}/messages/{messageId}/reactions', [ReactionController::class, 'store']);
    Route::delete('/conversations/{conversationId}/messages/{messageId}/reactions/{emoji}', [ReactionController::class, 'destroy']);

    Route::post('/conversations/{conversationId}/read', [ReadReceiptController::class, 'store']);

    // Typing - specific rate limit
    Route::post('/conversations/{conversationId}/typing', [TypingController::class, 'store'])
        ->middleware('throttle:widget-typing');

    // Attachments - specific rate limit
    Route::post('/conversations/{conversationId}/attachments', [AttachmentController::class, 'store'])
        ->middleware('throttle:widget-attachments');

    Route::post('/users/{id}/block', [BlockController::class, 'store']);
    Route::delete('/users/{id}/block', [BlockController::class, 'destroy']);
});

// Dashboard API - Sanctum Auth
Route::prefix('dashboard')->group(function () {
    // Auth (public)
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:login');

    // Protected routes
    Route::middleware(['auth:sanctum', 'throttle:dashboard'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Workspaces
        Route::get('/workspaces', [WorkspaceController::class, 'index']);
        Route::post('/workspaces', [WorkspaceController::class, 'store']);
        Route::get('/workspaces/{id}', [WorkspaceController::class, 'show']);
        Route::patch('/workspaces/{id}', [WorkspaceController::class, 'update']);

        // Settings & Theme
        Route::get('/workspaces/{id}/settings', [SettingsController::class, 'show']);
        Route::patch('/workspaces/{id}/settings', [SettingsController::class, 'update']);
        Route::get('/workspaces/{id}/theme', [ThemeController::class, 'show']);
        Route::patch('/workspaces/{id}/theme', [ThemeController::class, 'update']);

        // API Keys
        Route::get('/workspaces/{id}/api-keys', [ApiKeyController::class, 'index']);
        Route::post('/workspaces/{id}/api-keys', [ApiKeyController::class, 'store']);
        Route::delete('/workspaces/{id}/api-keys/{keyId}', [ApiKeyController::class, 'destroy']);

        // Conversations & Messages
        Route::get('/workspaces/{id}/conversations', [DashboardConversationController::class, 'index']);
        Route::get('/workspaces/{id}/conversations/{convId}', [DashboardConversationController::class, 'show']);
        Route::delete('/workspaces/{id}/messages/{msgId}', [DashboardConversationController::class, 'destroyMessage']);

        // Users & Moderation
        Route::get('/workspaces/{id}/users', [DashboardUserController::class, 'index']);
        Route::post('/workspaces/{id}/users/{userId}/ban', [DashboardUserController::class, 'ban']);
        Route::delete('/workspaces/{id}/users/{userId}/ban', [DashboardUserController::class, 'unban']);

        // Analytics
        Route::get('/workspaces/{id}/analytics', [AnalyticsController::class, 'overview']);
        Route::get('/workspaces/{id}/analytics/messages', [AnalyticsController::class, 'messages']);
        Route::get('/workspaces/{id}/analytics/users', [AnalyticsController::class, 'users']);
    });
});

// Health check endpoint (for debugging Redis and Bunny CDN)
Route::get('/health', function () {
    $results = [
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'services' => [],
        'env' => [
            'CACHE_STORE' => env('CACHE_STORE', 'not set'),
            'QUEUE_CONNECTION' => env('QUEUE_CONNECTION', 'not set'),
            'SESSION_DRIVER' => env('SESSION_DRIVER', 'not set'),
            'REDIS_URL' => env('REDIS_URL') ? 'configured' : 'not set',
            'BUNNY_STORAGE_ENABLED' => env('BUNNY_STORAGE_ENABLED', false) ? 'true' : 'false',
            'RAILWAY_BUCKET' => env('BUCKET') ? 'configured' : 'not set',
        ],
    ];

    // Check Redis connectivity
    try {
        $redisClient = env('REDIS_CLIENT', 'phpredis');

        // Test Redis connection
        Redis::set('health_check', 'ok');
        $redisValue = Redis::get('health_check');
        Redis::del('health_check');

        $results['services']['redis'] = [
            'status' => $redisValue === 'ok' ? 'connected' : 'error',
            'client' => $redisClient,
        ];
    } catch (\Throwable $e) {
        $results['services']['redis'] = [
            'status' => 'error',
            'error' => $e->getMessage(),
            'class' => get_class($e),
        ];
    }

    // Check Cache driver
    try {
        $cacheDriver = config('cache.default');
        Cache::put('health_check', 'ok', 10);
        $cacheValue = Cache::get('health_check');
        Cache::forget('health_check');

        $results['services']['cache'] = [
            'status' => $cacheValue === 'ok' ? 'connected' : 'error',
            'driver' => $cacheDriver,
        ];
    } catch (\Throwable $e) {
        $results['services']['cache'] = [
            'status' => 'error',
            'driver' => config('cache.default'),
            'error' => $e->getMessage(),
        ];
    }

    // Check Queue driver
    $results['services']['queue'] = [
        'driver' => config('queue.default'),
    ];

    // Check Bunny CDN connectivity
    try {
        $bunnyEnabled = env('BUNNY_STORAGE_ENABLED', false);
        if ($bunnyEnabled) {
            $disk = Storage::disk('bunny');

            // Try to write a test file
            $testPath = 'health-check-' . time() . '.txt';
            $disk->put($testPath, 'health check');

            // Verify it exists
            $exists = $disk->exists($testPath);

            // Clean up
            $disk->delete($testPath);

            $results['services']['bunny'] = [
                'status' => $exists ? 'connected' : 'error',
                'cdn_url' => env('BUNNY_CDN_URL'),
                'storage_zone' => env('BUNNY_STORAGE_ZONE'),
            ];
        } else {
            $results['services']['bunny'] = [
                'status' => 'disabled',
                'message' => 'BUNNY_STORAGE_ENABLED is not set',
            ];
        }
    } catch (\Throwable $e) {
        $results['services']['bunny'] = [
            'status' => 'error',
            'error' => $e->getMessage(),
        ];
    }

    // Check Railway bucket storage
    try {
        $bucketEnabled = env('BUCKET');
        if ($bucketEnabled) {
            $disk = Storage::disk('railway');

            // Try to write a test file
            $testPath = 'health-check-' . time() . '.txt';
            $disk->put($testPath, 'health check');

            // Verify it exists
            $exists = $disk->exists($testPath);

            // Clean up
            $disk->delete($testPath);

            $results['services']['railway_bucket'] = [
                'status' => $exists ? 'connected' : 'error',
                'bucket' => env('BUCKET'),
                'endpoint' => env('ENDPOINT'),
            ];
        } else {
            $results['services']['railway_bucket'] = [
                'status' => 'disabled',
                'message' => 'BUCKET env var not set',
            ];
        }
    } catch (\Throwable $e) {
        $results['services']['railway_bucket'] = [
            'status' => 'error',
            'error' => $e->getMessage(),
        ];
    }

    // Check Session driver
    $results['services']['session'] = [
        'driver' => config('session.driver'),
    ];

    // Overall status
    $hasError = collect($results['services'])
        ->contains(fn($service) => ($service['status'] ?? 'ok') === 'error');

    $results['status'] = $hasError ? 'degraded' : 'ok';

    return response()->json($results);
});
