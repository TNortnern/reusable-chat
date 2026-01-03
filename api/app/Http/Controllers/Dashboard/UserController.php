<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Ban;
use App\Models\ChatUser;
use App\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index(Request $request, string $id): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $perPage = min($request->input('per_page', 20), 100);
        $search = $request->input('search');
        $status = $request->input('status');
        $type = $request->input('type');

        $query = ChatUser::where('workspace_id', $workspace->id)
            ->withCount('conversations')
            ->with(['ban' => function ($q) {
                $q->where(function ($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                });
            }]);

        // Search filter (use LOWER for case-insensitive search across databases)
        if ($search) {
            $searchLower = strtolower($search);
            $query->where(function ($q) use ($searchLower) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        // Status filter (online, active, inactive)
        if ($status) {
            $now = Carbon::now();
            switch ($status) {
                case 'online':
                    $query->where('last_seen_at', '>', $now->copy()->subMinutes(5));
                    break;
                case 'active':
                    $query->where('last_seen_at', '>', $now->copy()->subHours(24))
                          ->where('last_seen_at', '<=', $now->copy()->subMinutes(5));
                    break;
                case 'inactive':
                    $query->where(function ($q) use ($now) {
                        $q->whereNull('last_seen_at')
                          ->orWhere('last_seen_at', '<=', $now->copy()->subHours(24));
                    });
                    break;
            }
        }

        // Type filter (anonymous, registered)
        if ($type) {
            $query->where('is_anonymous', $type === 'anonymous');
        }

        $users = $query->orderByDesc('last_seen_at')->paginate($perPage);

        // Calculate stats
        $fiveMinutesAgo = Carbon::now()->subMinutes(5);
        $twentyFourHoursAgo = Carbon::now()->subHours(24);

        $totalUsers = ChatUser::where('workspace_id', $workspace->id)->count();
        $onlineUsers = ChatUser::where('workspace_id', $workspace->id)
            ->where('last_seen_at', '>', $fiveMinutesAgo)
            ->count();
        $activeToday = ChatUser::where('workspace_id', $workspace->id)
            ->where('last_seen_at', '>', $twentyFourHoursAgo)
            ->count();
        $anonymousUsers = ChatUser::where('workspace_id', $workspace->id)
            ->where('is_anonymous', true)
            ->count();
        $bannedUsers = Ban::where('workspace_id', $workspace->id)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->count();

        return response()->json([
            'users' => $users,
            'stats' => [
                'total_users' => $totalUsers,
                'online_users' => $onlineUsers,
                'active_today' => $activeToday,
                'anonymous_users' => $anonymousUsers,
                'banned_users' => $bannedUsers,
            ],
        ]);
    }

    public function ban(Request $request, string $id, string $userId): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $user = ChatUser::where('workspace_id', $workspace->id)
            ->where('id', $userId)
            ->firstOrFail();

        $ban = Ban::updateOrCreate(
            ['workspace_id' => $workspace->id, 'chat_user_id' => $user->id],
            [
                'reason' => $validated['reason'] ?? null,
                'expires_at' => $validated['expires_at'] ?? null,
            ]
        );

        return response()->json($ban, 201);
    }

    public function unban(Request $request, string $id, string $userId): JsonResponse
    {
        $workspace = $this->getWorkspace($request, $id);

        $user = ChatUser::where('workspace_id', $workspace->id)
            ->where('id', $userId)
            ->firstOrFail();

        Ban::where('workspace_id', $workspace->id)
            ->where('chat_user_id', $user->id)
            ->delete();

        return response()->json(null, 204);
    }

    private function getWorkspace(Request $request, string $id): Workspace
    {
        return Workspace::where('id', $id)
            ->whereHas('members', fn($q) => $q->where('admin_id', $request->user()->id))
            ->firstOrFail();
    }
}
