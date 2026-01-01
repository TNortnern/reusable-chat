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

        // Set the user resolver so Broadcast::auth() can find the user
        $request->setUserResolver(fn() => $user);

        return Broadcast::auth($request);
    }
}
