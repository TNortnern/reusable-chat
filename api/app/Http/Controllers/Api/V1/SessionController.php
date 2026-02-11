<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\ChatUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|uuid',
            'expires_in' => 'nullable|integer|min:60', // seconds
        ]);

        $workspace = $request->attributes->get('workspace');
        
        if (!$workspace) {
            return response()->json([
                'error' => 'Workspace not found in request',
                'code' => 'workspace_not_in_request'
            ], 401);
        }
        
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

    public function destroy(Request $request, string $id): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');
        
        if (!$workspace) {
            return response()->json([
                'error' => 'Workspace not found in request',
                'code' => 'workspace_not_in_request'
            ], 401);
        }
        
        $session = ChatSession::where('workspace_id', $workspace->id)
            ->where('id', $id)
            ->firstOrFail();

        $session->delete();

        return response()->json(null, 204);
    }
}
