<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\ChatUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    public function store(Request $request, string $id): JsonResponse
    {
        $user = $request->chatUser;
        $workspace = $request->workspace;

        $blockedUser = ChatUser::where('workspace_id', $workspace->id)
            ->where('id', $id)
            ->firstOrFail();

        if ($blockedUser->id === $user->id) {
            return response()->json(['error' => 'Cannot block yourself'], 422);
        }

        $block = Block::firstOrCreate([
            'blocker_id' => $user->id,
            'blocked_id' => $blockedUser->id,
        ]);

        return response()->json($block, 201);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->chatUser;
        $workspace = $request->workspace;

        $blockedUser = ChatUser::where('workspace_id', $workspace->id)
            ->where('id', $id)
            ->firstOrFail();

        $deleted = Block::where('blocker_id', $user->id)
            ->where('blocked_id', $blockedUser->id)
            ->delete();

        if (!$deleted) {
            return response()->json(['error' => 'Block not found'], 404);
        }

        return response()->json(null, 204);
    }
}
