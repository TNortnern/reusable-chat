<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function show(Request $request, string $id): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);
        return response()->json($workspace->theme);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $validated = $request->validate([
            'preset' => 'nullable|in:minimal,playful,professional,custom',
            'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'background_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'font_family' => 'nullable|string|max:100',
            'logo_url' => 'nullable|url|max:500',
            'position' => 'nullable|in:bottom-right,bottom-left',
            'custom_css' => 'nullable|string|max:10000',
            'dark_mode_enabled' => 'nullable|boolean',
        ]);

        $workspace->theme->update(array_filter($validated, fn($v) => $v !== null));
        return response()->json($workspace->theme);
    }

    private function getWorkspace(Request $request, string $id): Workspace
    {
        return Workspace::where('id', $id)
            ->whereHas('members', fn($q) => $q->where('admin_id', $request->user()->id))
            ->with('theme')
            ->firstOrFail();
    }
}
