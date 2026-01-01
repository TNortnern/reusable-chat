<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $user = $request->chatUser;
        $workspace = $request->workspace;

        $file = $validated['file'];
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Store file (configure disk for Bunny CDN in production)
        $path = $file->storeAs(
            "workspaces/{$workspace->id}/attachments",
            $filename,
            'public'
        );

        $attachment = Attachment::create([
            'workspace_id' => $workspace->id,
            'uploaded_by' => $user->id,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'url' => Storage::disk('public')->url($path),
        ]);

        return response()->json($attachment, 201);
    }
}
