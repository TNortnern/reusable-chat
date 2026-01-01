<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ChatUser;
use App\Models\Participant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $workspace = $request->workspace;
        $user = ChatUser::where('workspace_id', $workspace->id)
            ->where('id', $validated['user_id'])
            ->firstOrFail();

        $conversations = Conversation::where('workspace_id', $workspace->id)
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('chat_user_id', $user->id);
            })
            ->with(['participants.chatUser', 'lastMessage'])
            ->orderByDesc('last_message_at')
            ->paginate($validated['per_page'] ?? 20);

        return response()->json($conversations);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:direct,group',
            'participant_ids' => 'required|array|min:2',
            'participant_ids.*' => 'uuid',
            'name' => 'nullable|string|max:255', // for group chats
        ]);

        $workspace = $request->workspace;

        // Validate all participants belong to this workspace
        $users = ChatUser::where('workspace_id', $workspace->id)
            ->whereIn('id', $validated['participant_ids'])
            ->get();

        if ($users->count() !== count($validated['participant_ids'])) {
            return response()->json(['error' => 'Invalid participant IDs'], 422);
        }

        // For direct chats, check if conversation already exists
        if ($validated['type'] === 'direct' && count($validated['participant_ids']) === 2) {
            $existing = Conversation::where('workspace_id', $workspace->id)
                ->where('type', 'direct')
                ->whereHas('participants', function ($q) use ($validated) {
                    $q->where('chat_user_id', $validated['participant_ids'][0]);
                })
                ->whereHas('participants', function ($q) use ($validated) {
                    $q->where('chat_user_id', $validated['participant_ids'][1]);
                })
                ->first();

            if ($existing) {
                return response()->json($existing->load('participants.chatUser'), 200);
            }
        }

        $conversation = Conversation::create([
            'workspace_id' => $workspace->id,
            'type' => $validated['type'],
            'name' => $validated['name'] ?? null,
        ]);

        foreach ($validated['participant_ids'] as $userId) {
            Participant::create([
                'conversation_id' => $conversation->id,
                'chat_user_id' => $userId,
            ]);
        }

        return response()->json($conversation->load('participants.chatUser'), 201);
    }

    public function addParticipant(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid',
        ]);

        $workspace = $request->workspace;

        $conversation = Conversation::where('workspace_id', $workspace->id)
            ->where('id', $id)
            ->where('type', 'group')
            ->firstOrFail();

        $user = ChatUser::where('workspace_id', $workspace->id)
            ->where('id', $validated['user_id'])
            ->firstOrFail();

        // Check if already participant
        $exists = Participant::where('conversation_id', $conversation->id)
            ->where('chat_user_id', $user->id)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'User is already a participant'], 422);
        }

        Participant::create([
            'conversation_id' => $conversation->id,
            'chat_user_id' => $user->id,
        ]);

        return response()->json($conversation->load('participants.chatUser'));
    }
}
