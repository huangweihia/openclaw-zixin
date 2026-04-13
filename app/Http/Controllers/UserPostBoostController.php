<?php

namespace App\Http\Controllers;

use App\Models\UserPost;
use App\Services\UserPostBoostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserPostBoostController extends Controller
{
    public function store(Request $request, UserPost $userPost): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $result = UserPostBoostService::boost($user, $userPost);

        if ($request->expectsJson()) {
            return response()->json(
                [
                    'ok' => (bool) $result['ok'],
                    'message' => (string) ($result['message'] ?? ''),
                ],
                $result['ok'] ? 200 : 422
            );
        }

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->with('error', $result['message']);
    }
}
