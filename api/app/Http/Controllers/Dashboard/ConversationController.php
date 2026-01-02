<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request, string $id): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $conversations = Conversation::where('workspace_id', $workspace->id)
            ->with(['participants', 'lastMessage'])
            ->withCount('messages')
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return response()->json($conversations);
    }

    public function show(Request $request, string $id, string $convId): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $conversation = Conversation::where('workspace_id', $workspace->id)
            ->where('id', $convId)
            ->with(['participants'])
            ->firstOrFail();

        $messages = $conversation->messages()
            ->with(['sender', 'attachments', 'reactions'])
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json([
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    public function destroyMessage(Request $request, string $id, string $msgId): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $message = Message::whereHas('conversation', function ($q) use ($workspace) {
            $q->where('workspace_id', $workspace->id);
        })->where('id', $msgId)->firstOrFail();

        $message->delete(); // Soft delete

        return response()->json(null, 204);
    }

    private function getWorkspace(Request $request, string $id): Workspace
    {
        return Workspace::where('id', $id)
            ->whereHas('members', fn($q) => $q->where('admin_id', $request->user()->id))
            ->firstOrFail();
    }
}
