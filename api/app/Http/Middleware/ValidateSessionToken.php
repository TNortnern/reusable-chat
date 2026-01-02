<?php

namespace App\Http\Middleware;

use App\Models\ChatSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSessionToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // Accept token from: Bearer header, or request body (for Pusher auth)
        $token = $request->bearerToken() ?: $request->input('auth_token');

        if (!$token) {
            return response()->json(['error' => 'Session token required'], 401);
        }

        $session = ChatSession::where('token', $token)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with(['chatUser', 'workspace'])
            ->first();

        if (!$session) {
            return response()->json(['error' => 'Invalid or expired session'], 401);
        }

        $request->merge([
            'chatSession' => $session,
            'chatUser' => $session->chatUser,
            'workspace' => $session->workspace,
        ]);

        return $next($request);
    }
}
