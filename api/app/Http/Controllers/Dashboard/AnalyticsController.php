<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Models\Message;
use App\Models\ChatUser;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function overview(Request $request, string $id): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        return response()->json([
            'total_users' => ChatUser::where('workspace_id', $workspace->id)->count(),
            'total_conversations' => Conversation::where('workspace_id', $workspace->id)->count(),
            'total_messages' => Message::whereHas('conversation', fn($q) =>
                $q->where('workspace_id', $workspace->id))->count(),
            'active_users_today' => ChatUser::where('workspace_id', $workspace->id)
                ->where('last_seen_at', '>=', now()->startOfDay())->count(),
        ]);
    }

    public function messages(Request $request, string $id): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $validated = $request->validate([
            'days' => 'nullable|integer|min:1|max:365',
        ]);
        $days = $validated['days'] ?? 7;

        $stats = Message::whereHas('conversation', fn($q) =>
                $q->where('workspace_id', $workspace->id))
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($stats);
    }

    public function users(Request $request, string $id): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $validated = $request->validate([
            'days' => 'nullable|integer|min:1|max:365',
        ]);
        $days = $validated['days'] ?? 7;

        $stats = ChatUser::where('workspace_id', $workspace->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($stats);
    }

    private function getWorkspace(Request $request, string $id): Workspace
    {
        return Workspace::where('id', $id)
            ->whereHas('members', fn($q) => $q->where('admin_id', $request->user()->id))
            ->firstOrFail();
    }
}
