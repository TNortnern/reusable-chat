<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    /**
     * Create a demo room (group conversation).
     * This allows creating a room without requiring participants upfront.
     */
    public function createRoom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
        ]);

        $workspace = $request->workspace;

        $conversation = Conversation::create([
            'workspace_id' => $workspace->id,
            'type' => 'group',
            'name' => $validated['name'] ?? 'Demo Room ' . now()->format('M j, g:i A'),
        ]);

        return response()->json([
            'id' => $conversation->id,
            'name' => $conversation->name,
            'type' => $conversation->type,
            'created_at' => $conversation->created_at,
        ], 201);
    }
}
