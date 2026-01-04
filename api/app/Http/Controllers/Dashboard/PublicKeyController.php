<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PublicKey;
use Illuminate\Http\Request;

class PublicKeyController extends Controller
{
    public function index(string $workspaceId)
    {
        $workspace = auth()->user()->workspaces()->findOrFail($workspaceId);

        return response()->json([
            'data' => $workspace->publicKeys()->orderBy('created_at', 'desc')->get()
        ]);
    }

    public function store(Request $request, string $workspaceId)
    {
        $workspace = auth()->user()->workspaces()->findOrFail($workspaceId);

        $request->validate([
            'name' => 'required|string|max:255',
            'allowed_origins' => 'nullable|array',
            'allowed_origins.*' => 'url',
            'settings' => 'nullable|array',
        ]);

        $publicKey = $workspace->publicKeys()->create([
            'key' => PublicKey::generateKey(),
            'name' => $request->name,
            'allowed_origins' => $request->allowed_origins,
            'settings' => $request->settings,
        ]);

        return response()->json($publicKey, 201);
    }

    public function update(Request $request, string $workspaceId, string $keyId)
    {
        $workspace = auth()->user()->workspaces()->findOrFail($workspaceId);
        $publicKey = $workspace->publicKeys()->findOrFail($keyId);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'allowed_origins' => 'nullable|array',
            'allowed_origins.*' => 'url',
            'settings' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $publicKey->update($request->only(['name', 'allowed_origins', 'settings', 'is_active']));

        return response()->json($publicKey);
    }

    public function destroy(string $workspaceId, string $keyId)
    {
        $workspace = auth()->user()->workspaces()->findOrFail($workspaceId);
        $workspace->publicKeys()->findOrFail($keyId)->delete();

        return response()->json(['message' => 'Public key deleted']);
    }
}
