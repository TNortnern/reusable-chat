<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkspaceRealtimeTokenRequest;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;

class WorkspaceController extends Controller
{
    public function realtimeToken(WorkspaceRealtimeTokenRequest $request, Workspace $workspace): JsonResponse
    {
        // Get the workspace owner to generate a token
        // API keys have workspace-level access, so we use the owner's admin account
        $admin = $workspace->owner;

        if (!$admin) {
            return response()->json(['error' => 'Workspace has no owner'], 500);
        }

        // Generate token with realtime:subscribe ability (expires in 24 hours)
        $token = $admin->createToken(
            'realtime-access',
            ['realtime:subscribe'],
            now()->addDay()
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at->toIso8601String(),
            'workspace_id' => $workspace->id,
            'channels' => ["private-workspace.{$workspace->id}"],
        ]);
    }
}
