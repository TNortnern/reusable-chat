<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReadReceiptController extends Controller
{
    public function store(Request $request, string $conversationId): JsonResponse
    {
        $user = $request->chatUser;
        $workspace = $request->workspace;

        $conversation = Conversation::where('workspace_id', $workspace->id)
            ->where('id', $conversationId)
            ->whereHas('participants', fn($q) => $q->where('chat_user_id', $user->id))
            ->firstOrFail();

        Participant::where('conversation_id', $conversation->id)
            ->where('chat_user_id', $user->id)
            ->update(['last_read_at' => now()]);

        // TODO: Broadcast MessagesRead event

        return response()->json(['read_at' => now()]);
    }
}
