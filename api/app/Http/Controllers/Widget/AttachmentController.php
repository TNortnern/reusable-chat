<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    public function store(Request $request, string $conversationId): JsonResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,txt',
        ]);

        $user = $request->chatUser;
        $workspace = $request->workspace;

        // Verify user has access to conversation
        $conversation = Conversation::where('workspace_id', $workspace->id)
            ->where('id', $conversationId)
            ->whereHas('participants', fn($q) => $q->where('chat_user_id', $user->id))
            ->firstOrFail();

        $file = $validated['file'];
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Use Bunny CDN if enabled, otherwise local public disk
        $disk = env('BUNNY_STORAGE_ENABLED') ? 'bunny' : 'public';
        $storagePath = "workspaces/{$workspace->id}/attachments/{$filename}";

        \Illuminate\Support\Facades\Storage::disk($disk)->put($storagePath, file_get_contents($file));
        $path = $storagePath;

        $attachment = Attachment::create([
            'workspace_id' => $workspace->id,
            'conversation_id' => $conversation->id,
            'chat_user_id' => $user->id,
            'name' => $file->getClientOriginalName(),
            'type' => $file->getMimeType(),
            'path' => $path,
            'size' => $file->getSize(),
        ]);

        return response()->json($attachment, 201);
    }
}
