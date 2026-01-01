<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\SessionController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\ModerationController;
use App\Http\Controllers\Widget\MeController;
use App\Http\Controllers\Widget\ConversationController as WidgetConversationController;
use App\Http\Controllers\Widget\MessageController;
use App\Http\Controllers\Widget\ReactionController;
use App\Http\Controllers\Widget\ReadReceiptController;
use App\Http\Controllers\Widget\TypingController;
use App\Http\Controllers\Widget\AttachmentController;
use App\Http\Controllers\Widget\BlockController;

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
});

// Widget API - Session Token Auth
Route::prefix('widget')->middleware('session.token')->group(function () {
    Route::get('/me', [MeController::class, 'show']);

    Route::get('/conversations', [WidgetConversationController::class, 'index']);
    Route::post('/conversations', [WidgetConversationController::class, 'store']);
    Route::get('/conversations/{id}', [WidgetConversationController::class, 'show']);

    Route::post('/conversations/{conversationId}/messages', [MessageController::class, 'store']);
    Route::post('/conversations/{conversationId}/messages/{messageId}/reactions', [ReactionController::class, 'store']);
    Route::delete('/conversations/{conversationId}/messages/{messageId}/reactions/{emoji}', [ReactionController::class, 'destroy']);

    Route::post('/conversations/{conversationId}/read', [ReadReceiptController::class, 'store']);
    Route::post('/conversations/{conversationId}/typing', [TypingController::class, 'store']);

    Route::post('/attachments', [AttachmentController::class, 'store']);

    Route::post('/users/{id}/block', [BlockController::class, 'store']);
    Route::delete('/users/{id}/block', [BlockController::class, 'destroy']);
});
