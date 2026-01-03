<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Conversation;
use App\Services\TnFilesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    public function __construct(
        protected TnFilesService $tnFilesService
    ) {}

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
        $storagePath = "workspaces/{$workspace->id}/attachments";

        // Try tn-files service first (preferred for production)
        if (config('services.tn_files.api_key')) {
            $result = $this->tnFilesService->upload($file, $storagePath);

            if ($result['success']) {
                $attachment = Attachment::create([
                    'workspace_id' => $workspace->id,
                    'conversation_id' => $conversation->id,
                    'chat_user_id' => $user->id,
                    'name' => $result['original_name'],
                    'type' => $result['mime_type'],
                    'path' => "{$storagePath}/{$result['filename']}",
                    'size' => $result['size'],
                    // Legacy fields
                    'filename' => $result['original_name'],
                    'mime_type' => $result['mime_type'],
                    'size_bytes' => $result['size'],
                    'url' => $result['url'],
                ]);

                return response()->json($attachment, 201);
            }

            // Log the error but fall back to other storage options
            \Log::warning('TnFiles upload failed', ['error' => $result['error']]);
        }

        // Fallback: Determine storage disk: Railway bucket > Bunny CDN > local public
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $filename = Str::uuid() . '.' . $extension;

        if (env('BUCKET')) {
            $disk = 'railway';
        } elseif (env('BUNNY_STORAGE_ENABLED')) {
            $disk = 'bunny';
        } else {
            $disk = 'public';
        }

        // Use putFileAs for proper file handling with streams
        $path = \Illuminate\Support\Facades\Storage::disk($disk)->putFileAs(
            $storagePath,
            $file,
            $filename
        );

        if (!$path) {
            return response()->json(['error' => 'Failed to upload file'], 500);
        }

        // Generate the public URL for the uploaded file
        $url = \Illuminate\Support\Facades\Storage::disk($disk)->url($path);

        $attachment = Attachment::create([
            'workspace_id' => $workspace->id,
            'conversation_id' => $conversation->id,
            'chat_user_id' => $user->id,
            'name' => $file->getClientOriginalName(),
            'type' => $file->getMimeType(),
            'path' => $path,
            'size' => $file->getSize(),
            // Legacy fields
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'url' => $url,
        ]);

        return response()->json($attachment, 201);
    }
}
