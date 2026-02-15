<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->attributes->get('chatUser');
        $workspace = $request->attributes->get('workspace');

        if (!$workspace) {
            return response()->json([
                'error' => 'Workspace not found in request',
                'code' => 'workspace_not_in_request'
            ], 401);
        }

        return response()->json([
            'user' => $user,
            'settings' => $workspace->settings,
            'theme' => $workspace->theme,
        ]);
    }
}
