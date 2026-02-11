<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChatUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'external_id' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'avatar_url' => 'nullable|url|max:500',
            'metadata' => 'nullable|array',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $workspace = $request->attributes->get('workspace');
        
        if (!$workspace) {
            return response()->json([
                'error' => 'Workspace not found in request',
                'code' => 'workspace_not_in_request'
            ], 401);
        }

        $user = ChatUser::updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'external_id' => $validated['external_id'],
            ],
            [
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'] ?? null,
                'avatar_url' => $validated['avatar_url'] ?? null,
                'metadata' => $validated['metadata'] ?? [],
                'is_anonymous' => $validated['is_anonymous'] ?? false,
            ]
        );

        return response()->json($user, $user->wasRecentlyCreated ? 201 : 200);
    }

    public function show(Request $request, string $externalId): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');
        
        if (!$workspace) {
            return response()->json([
                'error' => 'Workspace not found in request',
                'code' => 'workspace_not_in_request'
            ], 401);
        }
        
        $user = ChatUser::where('workspace_id', $workspace->id)
            ->where('external_id', $externalId)
            ->firstOrFail();

        return response()->json($user);
    }
}
