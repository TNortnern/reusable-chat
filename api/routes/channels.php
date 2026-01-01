<?php

use App\Models\Conversation;
use App\Models\ChatUser;
use App\Models\Admin;
use App\Models\Participant;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Conversation channel - user must be a participant
Broadcast::channel('conversation.{conversationId}', function ($user, string $conversationId) {
    // $user can be ChatUser (widget) or Admin (dashboard)

    if ($user instanceof ChatUser) {
        // Widget user - must be a participant
        return Participant::where('conversation_id', $conversationId)
            ->where('chat_user_id', $user->id)
            ->exists();
    }

    if ($user instanceof Admin) {
        // Dashboard admin - must have access to the conversation's workspace
        $conversation = Conversation::find($conversationId);
        if (!$conversation) {
            return false;
        }

        return $conversation->workspace->members()
            ->where('admin_id', $user->id)
            ->exists();
    }

    return false;
});

// User channel - only that specific user can subscribe
Broadcast::channel('user.{userId}', function ($user, string $userId) {
    if ($user instanceof ChatUser) {
        return $user->id === $userId;
    }

    // Admins don't subscribe to user channels
    return false;
});
