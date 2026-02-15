<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BroadcastAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $user = $request->attributes->get('chatUser');

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $socketId = $request->input('socket_id');
        $channelName = $request->input('channel_name');

        if (!$socketId) {
            return response()->json(['error' => 'socket_id required'], 400);
        }

        if (!$channelName) {
            return response()->json(['error' => 'channel_name required'], 400);
        }

        $pusher = new \Pusher\Pusher(
            config('broadcasting.connections.reverb.key'),
            config('broadcasting.connections.reverb.secret'),
            config('broadcasting.connections.reverb.app_id'),
            [
                'host' => config('broadcasting.connections.reverb.options.host'),
                'port' => config('broadcasting.connections.reverb.options.port'),
                'scheme' => config('broadcasting.connections.reverb.options.scheme'),
                'useTLS' => config('broadcasting.connections.reverb.options.useTLS'),
            ]
        );

        try {
            $auth = $pusher->socket_auth($channelName, $socketId);
            return response($auth, 200)->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            Log::error('Broadcast auth error: ' . $e->getMessage());
            return response()->json(['error' => 'Authorization failed'], 500);
        }
    }
}
