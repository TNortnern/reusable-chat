<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Ban;
use App\Models\ChatUser;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request, string $id): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $users = ChatUser::where('workspace_id', $workspace->id)
            ->withCount('conversations')
            ->orderByDesc('last_seen_at')
            ->paginate(20);

        return response()->json($users);
    }

    public function ban(Request $request, string $id, string $userId): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $user = ChatUser::where('workspace_id', $workspace->id)
            ->where('id', $userId)
            ->firstOrFail();

        $ban = Ban::updateOrCreate(
            ['workspace_id' => $workspace->id, 'chat_user_id' => $user->id],
            [
                'reason' => $validated['reason'] ?? null,
                'expires_at' => $validated['expires_at'] ?? null,
            ]
        );

        return response()->json($ban, 201);
    }

    public function unban(Request $request, string $id, string $userId): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $user = ChatUser::where('workspace_id', $workspace->id)
            ->where('id', $userId)
            ->firstOrFail();

        Ban::where('workspace_id', $workspace->id)
            ->where('chat_user_id', $user->id)
            ->delete();

        return response()->json(null, 204);
    }

    private function getWorkspace(Request $request, string $id): Workspace
    {
        return Workspace::where('id', $id)
            ->whereHas('members', fn($q) => $q->where('admin_id', $request->user()->id))
            ->firstOrFail();
    }
}
