<?php

namespace App\Http\Controllers;

use App\Models\UserPost;
use App\Services\UserPostBoostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserPostBoostController extends Controller
{
    public function store(Request $request, UserPost $userPost): RedirectResponse
    {
        $user = $request->user();
        $result = UserPostBoostService::boost($user, $userPost);

        return $result['ok']
            ? back()->with('success', $result['message'])
            : back()->with('error', $result['message']);
    }
}
