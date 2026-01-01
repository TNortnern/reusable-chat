<?php

namespace App\Http\Controllers\Widget;

use App\Events\MessageCreated;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request, string $conversationId): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required_without:attachment_ids|string|max:5000',
            'attachment_ids' => 'nullable|array',
            'attachment_ids.*' => 'uuid',
        ]);

        $user = $request->chatUser;
        $workspace = $request->workspace;

        $conversation = Conversation::where('workspace_id', $workspace->id)
            ->where('id', $conversationId)
            ->whereHas('participants', fn($q) => $q->where('chat_user_id', $user->id))
            ->firstOrFail();

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'content' => $validated['content'] ?? '',
        ]);

        // Link attachments if provided
        if (!empty($validated['attachment_ids'])) {
            $attachedCount = \App\Models\Attachment::whereIn('id', $validated['attachment_ids'])
                ->whereNull('message_id')
                ->update(['message_id' => $message->id]);

            if ($attachedCount !== count($validated['attachment_ids'])) {
                // Some attachments were invalid or already used
                // Continue anyway - partial success is acceptable
            }
        }

        // Update conversation last_message_at
        $conversation->update(['last_message_at' => now()]);

        $message->load(['sender', 'attachments']);
        broadcast(new MessageCreated($message))->toOthers();

        return response()->json($message, 201);
    }
}
