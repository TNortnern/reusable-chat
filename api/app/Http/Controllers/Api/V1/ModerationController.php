<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ban;
use App\Models\ChatUser;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function ban(Request $request, string $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $workspace = $request->workspace;

        $user = ChatUser::where('workspace_id', $workspace->id)
            ->where('id', $id)
            ->firstOrFail();

        // Check if already banned
        $existingBan = Ban::where('workspace_id', $workspace->id)
            ->where('chat_user_id', $user->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($existingBan) {
            return response()->json(['error' => 'User is already banned'], 422);
        }

        $ban = Ban::create([
            'workspace_id' => $workspace->id,
            'chat_user_id' => $user->id,
            'reason' => $validated['reason'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return response()->json($ban, 201);
    }

    public function unban(Request $request, string $id)
    {
        $workspace = $request->workspace;

        $user = ChatUser::where('workspace_id', $workspace->id)
            ->where('id', $id)
            ->firstOrFail();

        $deleted = Ban::where('workspace_id', $workspace->id)
            ->where('chat_user_id', $user->id)
            ->delete();

        if (!$deleted) {
            return response()->json(['error' => 'User is not banned'], 404);
        }

        return response()->json(null, 204);
    }

    public function index(Request $request)
    {
        $workspace = $request->workspace;

        $bans = Ban::where('workspace_id', $workspace->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('chatUser')
            ->paginate(20);

        return response()->json($bans);
    }
}
