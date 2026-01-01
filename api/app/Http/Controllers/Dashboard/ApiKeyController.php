<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    public function index(Request $request, string $id): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);
        $keys = ApiKey::where('workspace_id', $workspace->id)
            ->whereNull('revoked_at')
            ->get();

        return response()->json($keys);
    }

    public function store(Request $request, string $id): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $plainKey = 'sk_live_' . Str::random(32);

        $apiKey = ApiKey::create([
            'workspace_id' => $workspace->id,
            'name' => $validated['name'],
            'key_hash' => hash('sha256', $plainKey),
            'key_prefix' => substr($plainKey, 0, 12) . '...',
        ]);

        return response()->json([
            'id' => $apiKey->id,
            'name' => $apiKey->name,
            'key' => $plainKey, // Only shown once!
            'key_prefix' => $apiKey->key_prefix,
            'created_at' => $apiKey->created_at,
        ], 201);
    }

    public function destroy(Request $request, string $id, string $keyId): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $key = ApiKey::where('workspace_id', $workspace->id)
            ->where('id', $keyId)
            ->firstOrFail();

        $key->update(['revoked_at' => now()]);
        return response()->json(null, 204);
    }

    private function getWorkspace(Request $request, string $id): Workspace
    {
        return Workspace::where('id', $id)
            ->whereHas('members', fn($q) => $q->where('admin_id', $request->user()->id))
            ->firstOrFail();
    }
}
