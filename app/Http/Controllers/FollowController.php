<?php

namespace App\Http\Controllers;

use App\Models\InboxNotification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function store(Request $request, User $user): JsonResponse
    {
        if ((int) $user->id === (int) $request->user()->id) {
            return response()->json(['ok' => false, 'message' => '不能关注自己'], 422);
        }

        $me = $request->user();
        if ($me->following()->whereKey($user->id)->exists()) {
            return response()->json(['ok' => true, 'is_following' => true, 'message' => '已关注']);
        }

        $me->following()->attach($user->id);

        InboxNotification::query()->create([
            'user_id' => $user->id,
            'type' => 'follow_received',
            'title' => '「'.$me->name.'」关注了你',
            'content' => '你有了新的关注者，可在个人中心查看粉丝列表。',
            'action_url' => null,
        ]);

        return response()->json(['ok' => true, 'is_following' => true, 'message' => '已关注']);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $request->user()->following()->detach($user->id);

        return response()->json(['ok' => true, 'is_following' => false, 'message' => '已取消关注']);
    }
}
