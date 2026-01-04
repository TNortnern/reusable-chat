<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Report a message for moderation
     */
    public function store(Request $request, string $conversationId, string $messageId): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|in:spam,harassment,inappropriate,other',
            'description' => 'nullable|string|max:1000',
        ]);

        $user = $request->chatUser;
        $workspace = $request->workspace;

        // Verify user is participant in conversation
        $conversation = Conversation::where('workspace_id', $workspace->id)
            ->where('id', $conversationId)
            ->whereHas('participants', fn($q) => $q->where('chat_user_id', $user->id))
            ->firstOrFail();

        // Verify message exists in conversation
        $message = Message::where('conversation_id', $conversation->id)
            ->where('id', $messageId)
            ->firstOrFail();

        // Don't allow reporting your own messages
        if ($message->sender_id === $user->id) {
            return response()->json(['error' => 'Cannot report your own message'], 422);
        }

        // Check if already reported by this user
        $existingReport = Report::where('message_id', $messageId)
            ->where('reporter_id', $user->id)
            ->first();

        if ($existingReport) {
            return response()->json(['error' => 'You have already reported this message'], 422);
        }

        // Create report
        $report = Report::create([
            'workspace_id' => $workspace->id,
            'message_id' => $messageId,
            'conversation_id' => $conversationId,
            'reporter_id' => $user->id,
            'reported_user_id' => $message->sender_id,
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
        ]);

        // Log for admin notification
        Log::info('Message reported', [
            'report_id' => $report->id,
            'workspace_id' => $workspace->id,
            'message_id' => $messageId,
            'reporter_id' => $user->id,
            'reason' => $validated['reason'],
        ]);

        return response()->json([
            'message' => 'Report submitted successfully',
            'report_id' => $report->id,
        ], 201);
    }
}
