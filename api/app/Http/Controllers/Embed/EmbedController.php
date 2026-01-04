<?php

namespace App\Http\Controllers\Embed;

use App\Http\Controllers\Controller;
use App\Models\PublicKey;
use App\Models\ChatUser;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmbedController extends Controller
{
    /**
     * Initialize widget - validates public key and returns config
     */
    public function init(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
        ]);

        $publicKey = PublicKey::where('key', $request->key)
            ->where('is_active', true)
            ->first();

        if (!$publicKey) {
            return response()->json(['error' => 'Invalid public key'], 401);
        }

        // Check CORS if allowed_origins is set
        $origin = $request->header('Origin');
        if ($publicKey->allowed_origins && !in_array($origin, $publicKey->allowed_origins)) {
            return response()->json(['error' => 'Origin not allowed'], 403);
        }

        // Update last used
        $publicKey->update(['last_used_at' => now()]);

        // Get workspace settings for widget
        $workspace = $publicKey->workspace;
        $theme = $workspace->theme ?? [];

        return response()->json([
            'workspace_id' => $workspace->id,
            'workspace_name' => $workspace->name,
            'settings' => array_merge([
                'position' => 'bottom-right',
                'theme' => 'light',
                'show_branding' => true,
            ], $publicKey->settings ?? []),
            'theme' => [
                'primary_color' => $theme['primary_color'] ?? '#667eea',
                'secondary_color' => $theme['secondary_color'] ?? '#764ba2',
            ],
        ]);
    }

    /**
     * Create session for widget user
     */
    public function session(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'user_id' => 'required|string',
            'user_name' => 'required|string',
            'user_email' => 'nullable|email',
            'user_avatar' => 'nullable|url',
        ]);

        $publicKey = PublicKey::where('key', $request->key)
            ->where('is_active', true)
            ->first();

        if (!$publicKey) {
            return response()->json(['error' => 'Invalid public key'], 401);
        }

        $workspace = $publicKey->workspace;

        // Find or create chat user
        $chatUser = ChatUser::firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'external_id' => $request->user_id,
            ],
            [
                'name' => $request->user_name,
                'email' => $request->user_email,
                'avatar_url' => $request->user_avatar,
            ]
        );

        // Update name if changed
        if ($chatUser->name !== $request->user_name) {
            $chatUser->update(['name' => $request->user_name]);
        }

        // Create session
        $session = Session::create([
            'chat_user_id' => $chatUser->id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'token' => $session->token,
            'user' => [
                'id' => $chatUser->id,
                'name' => $chatUser->name,
                'email' => $chatUser->email,
            ],
            'expires_at' => $session->expires_at->toISOString(),
        ]);
    }
}
