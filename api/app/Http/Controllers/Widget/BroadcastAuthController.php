<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;

class BroadcastAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        // The session.token middleware sets $request->chatUser
        $user = $request->chatUser;

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Authorize the channel subscription
        return Broadcast::auth($request);
    }
}
