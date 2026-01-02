<?php

namespace App\Http\Controllers\Widget;

use App\Events\ConversationCreated;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->chatUser;

        $conversations = Conversation::where('workspace_id', $request->workspace->id)
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('chat_user_id', $user->id);
            })
            ->with(['participants', 'lastMessage'])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        // Add unread count based on participant's last_read_at timestamp
        $conversations->getCollection()->transform(function ($conversation) use ($user) {
            $participant = $conversation->participants->firstWhere('chat_user_id', $user->id);
            $lastReadAt = $participant?->last_read_at;

            if ($lastReadAt) {
                $conversation->unread_count = $conversation->messages()
                    ->where('created_at', '>', $lastReadAt)
                    ->where('sender_id', '!=', $user->id)
                    ->count();
            } else {
                // Never read - all messages from others are unread
                $conversation->unread_count = $conversation->messages()
                    ->where('sender_id', '!=', $user->id)
                    ->count();
            }

            return $conversation;
        });

        return response()->json($conversations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:direct,group',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'uuid',
            'name' => 'nullable|string|max:255',
        ]);

        $workspace = $request->workspace;
        $currentUser = $request->chatUser;

        // Add current user to participants
        $participantIds = array_unique(array_merge(
            $validated['participant_ids'],
            [$currentUser->id]
        ));

        // Validate all participants exist in workspace
        $validUsers = \App\Models\ChatUser::where('workspace_id', $workspace->id)
            ->whereIn('id', $participantIds)
            ->pluck('id')
            ->toArray();

        if (count($validUsers) !== count($participantIds)) {
            return response()->json(['error' => 'Invalid participant IDs'], 422);
        }

        // For direct chats, check if exists
        if ($validated['type'] === 'direct' && count($participantIds) === 2) {
            $existing = Conversation::where('workspace_id', $workspace->id)
                ->where('type', 'direct')
                ->whereHas('participants', fn($q) => $q->where('chat_user_id', $participantIds[0]))
                ->whereHas('participants', fn($q) => $q->where('chat_user_id', $participantIds[1]))
                ->first();

            if ($existing) {
                return response()->json($existing->load('participants'), 200);
            }
        }

        $conversation = Conversation::create([
            'workspace_id' => $workspace->id,
            'type' => $validated['type'],
            'name' => $validated['name'] ?? null,
        ]);

        foreach ($participantIds as $userId) {
            Participant::create([
                'conversation_id' => $conversation->id,
                'chat_user_id' => $userId,
            ]);
        }

        $conversation->load('participants');

        // Notify all other participants about the new conversation
        foreach ($participantIds as $participantId) {
            if ($participantId !== $currentUser->id) {
                broadcast(new ConversationCreated($conversation, $participantId));
            }
        }

        return response()->json($conversation->load('participants'), 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->chatUser;

        $conversation = Conversation::where('workspace_id', $request->workspace->id)
            ->where('id', $id)
            ->whereHas('participants', fn($q) => $q->where('chat_user_id', $user->id))
            ->with(['participants'])
            ->firstOrFail();

        $messages = $conversation->messages()
            ->with(['sender', 'attachments', 'reactions'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }
}
