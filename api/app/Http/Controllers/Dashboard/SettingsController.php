<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function show(Request $request, string $id): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);
        return response()->json($workspace->settings);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $validated = $request->validate([
            'read_receipts_enabled' => 'nullable|boolean',
            'online_status_enabled' => 'nullable|boolean',
            'typing_indicators_enabled' => 'nullable|boolean',
            'file_size_limit_mb' => 'nullable|integer|min:1|max:50',
            'rate_limit_per_minute' => 'nullable|integer|min:10|max:300',
            'webhook_url' => 'nullable|url|max:500',
            'webhook_secret' => 'nullable|string|max:100',
        ]);

        $nullableFields = ['webhook_url', 'webhook_secret'];
        $updateData = [];

        foreach ($validated as $key => $value) {
            if ($value !== null || ($request->has($key) && in_array($key, $nullableFields))) {
                $updateData[$key] = $value;
            }
        }

        $workspace->settings->update($updateData);
        return response()->json($workspace->settings);
    }

    private function getWorkspace(Request $request, string $id): Workspace
    {
        return Workspace::where('id', $id)
            ->whereHas('members', fn($q) => $q->where('admin_id', $request->user()->id))
            ->with('settings')
            ->firstOrFail();
    }
}
