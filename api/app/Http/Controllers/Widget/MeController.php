<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->chatUser;
        $workspace = $request->workspace;

        return response()->json([
            'user' => $user,
            'settings' => $workspace->settings,
            'theme' => $workspace->theme,
        ]);
    }
}
