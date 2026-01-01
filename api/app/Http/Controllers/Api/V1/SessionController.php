<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\ChatUser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid',
            'expires_in' => 'nullable|integer|min:60', // seconds
        ]);

        $workspace = $request->workspace;
        $user = ChatUser::where('workspace_id', $workspace->id)
            ->where('id', $validated['user_id'])
            ->firstOrFail();

        $session = ChatSession::create([
            'workspace_id' => $workspace->id,
            'chat_user_id' => $user->id,
            'token' => Str::random(64),
            'expires_at' => isset($validated['expires_in'])
                ? now()->addSeconds($validated['expires_in'])
                : null,
        ]);

        return response()->json([
            'id' => $session->id,
            'token' => $session->token,
            'expires_at' => $session->expires_at,
        ], 201);
    }

    public function destroy(Request $request, string $id)
    {
        $session = ChatSession::where('workspace_id', $request->workspace->id)
            ->where('id', $id)
            ->firstOrFail();

        $session->delete();

        return response()->json(null, 204);
    }
}
