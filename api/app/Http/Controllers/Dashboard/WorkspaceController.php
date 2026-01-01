<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Models\WorkspaceSettings;
use App\Models\WorkspaceTheme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $workspaces = Workspace::whereHas('members', function ($q) use ($request) {
            $q->where('admin_id', $request->user()->id);
        })->with('settings', 'theme')->paginate(20);

        return response()->json($workspaces);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:workspaces,slug',
        ]);

        $workspace = Workspace::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['name']),
            'owner_id' => $request->user()->id,
        ]);

        // Create default settings and theme
        WorkspaceSettings::create(['workspace_id' => $workspace->id]);
        WorkspaceTheme::create(['workspace_id' => $workspace->id]);

        // Add owner as member
        WorkspaceMember::create([
            'workspace_id' => $workspace->id,
            'admin_id' => $request->user()->id,
            'role' => 'owner',
        ]);

        return response()->json($workspace->load('settings', 'theme'), 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);
        return response()->json($workspace->load('settings', 'theme'));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:100|unique:workspaces,slug,' . $workspace->id,
        ]);

        $workspace->update(array_filter($validated));
        return response()->json($workspace);
    }

    private function getWorkspace(Request $request, string $id): Workspace
    {
        return Workspace::where('id', $id)
            ->whereHas('members', fn($q) => $q->where('admin_id', $request->user()->id))
            ->firstOrFail();
    }
}
