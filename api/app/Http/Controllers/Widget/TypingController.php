<?php

namespace App\Http\Controllers\Widget;

use App\Events\UserTyping;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TypingController extends Controller
{
    public function store(Request $request, string $conversationId): JsonResponse
    {
        $user = $request->chatUser;
        $workspace = $request->workspace;
        $isTyping = $request->input('is_typing', true);

        $conversation = Conversation::where('workspace_id', $workspace->id)
            ->where('id', $conversationId)
            ->whereHas('participants', fn($q) => $q->where('chat_user_id', $user->id))
            ->firstOrFail();

        broadcast(new UserTyping($user, $conversationId, $isTyping))->toOthers();

        return response()->json(['typing' => $isTyping]);
    }
}
