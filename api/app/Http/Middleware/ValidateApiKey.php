<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return response()->json(['error' => 'API key required'], 401);
        }

        $keyHash = hash('sha256', $apiKey);
        $key = ApiKey::where('key_hash', $keyHash)
            ->whereNull('revoked_at')
            ->first();

        if (!$key) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $key->update(['last_used_at' => now()]);

        // Store workspace in request attributes (not data bag to avoid serialization issues)
        $request->attributes->set('workspace', $key->workspace);
        $request->attributes->set('api_key', $key);

        return $next($request);
    }
}
