<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Reaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function store(Request $request, string $conversationId, string $messageId): JsonResponse
    {
        $validated = $request->validate([
            'emoji' => 'required|string|max:10',
        ]);

        $user = $request->chatUser;
        $workspace = $request->workspace;

        // Verify user has access to conversation
        $conversation = Conversation::where('workspace_id', $workspace->id)
            ->where('id', $conversationId)
            ->whereHas('participants', fn($q) => $q->where('chat_user_id', $user->id))
            ->firstOrFail();

        $message = Message::where('conversation_id', $conversation->id)
            ->where('id', $messageId)
            ->firstOrFail();

        $reaction = Reaction::firstOrCreate([
            'message_id' => $message->id,
            'chat_user_id' => $user->id,
            'emoji' => $validated['emoji'],
        ]);

        // TODO: Broadcast ReactionAdded event

        return response()->json($reaction, 201);
    }

    public function destroy(Request $request, string $conversationId, string $messageId, string $emoji): JsonResponse
    {
        $user = $request->chatUser;
        $workspace = $request->workspace;

        $conversation = Conversation::where('workspace_id', $workspace->id)
            ->where('id', $conversationId)
            ->whereHas('participants', fn($q) => $q->where('chat_user_id', $user->id))
            ->firstOrFail();

        $message = Message::where('conversation_id', $conversation->id)
            ->where('id', $messageId)
            ->firstOrFail();

        $deleted = Reaction::where('message_id', $message->id)
            ->where('chat_user_id', $user->id)
            ->where('emoji', $emoji)
            ->delete();

        if (!$deleted) {
            return response()->json(['error' => 'Reaction not found'], 404);
        }

        // TODO: Broadcast ReactionRemoved event

        return response()->json(null, 204);
    }
}
