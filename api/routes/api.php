<?php

use Illuminate\Support\Facades\Route;
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
Route::prefix('v1')->middleware('api.key')->group(function () {
    // Sessions
    Route::post('/sessions', [SessionController::class, 'store']);
    Route::delete('/sessions/{id}', [SessionController::class, 'destroy']);

    // Users
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{external_id}', [UserController::class, 'show']);

    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::post('/conversations/{id}/participants', [ConversationController::class, 'addParticipant']);

    // Moderation
    Route::post('/users/{id}/ban', [ModerationController::class, 'ban']);
    Route::delete('/users/{id}/ban', [ModerationController::class, 'unban']);
    Route::get('/bans', [ModerationController::class, 'index']);

    // Demo
    Route::post('/demo/rooms', [DemoController::class, 'createRoom']);
});

// Widget API - Session Token Auth
Route::prefix('widget')->middleware('session.token')->group(function () {
    Route::get('/me', [MeController::class, 'show']);

    // Broadcasting auth for Reverb
    Route::post('/broadcasting/auth', [BroadcastAuthController::class, 'authenticate']);

    Route::get('/conversations', [WidgetConversationController::class, 'index']);
    Route::post('/conversations', [WidgetConversationController::class, 'store']);
    Route::get('/conversations/{id}', [WidgetConversationController::class, 'show']);

    Route::post('/conversations/{conversationId}/messages', [MessageController::class, 'store']);
    Route::post('/conversations/{conversationId}/messages/{messageId}/reactions', [ReactionController::class, 'store']);
    Route::delete('/conversations/{conversationId}/messages/{messageId}/reactions/{emoji}', [ReactionController::class, 'destroy']);

    Route::post('/conversations/{conversationId}/read', [ReadReceiptController::class, 'store']);
    Route::post('/conversations/{conversationId}/typing', [TypingController::class, 'store']);
    Route::post('/conversations/{conversationId}/attachments', [AttachmentController::class, 'store']);

    Route::post('/users/{id}/block', [BlockController::class, 'store']);
    Route::delete('/users/{id}/block', [BlockController::class, 'destroy']);
});

// Dashboard API - Sanctum Auth
Route::prefix('dashboard')->group(function () {
    // Auth (public)
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1'); // 5 attempts per minute

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
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
