<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\SessionController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\ModerationController;

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
