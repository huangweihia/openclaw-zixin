<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PublicUserController extends Controller
{
    public function snippet(User $user): JsonResponse
    {
        $roleLabel = match ($user->role) {
            'admin' => '管理员',
            'svip' => 'SVIP',
            'vip' => 'VIP',
            default => '用户',
        };

        $me = Auth::user();
        $hasFollows = Schema::hasTable('user_follows');
        $isSelf = Auth::check() && (int) Auth::id() === (int) $user->id;
        $isFollowing = $hasFollows && $me && ! $isSelf
            ? $me->following()->whereKey($user->id)->exists()
            : false;

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar,
            'bio' => $user->bio,
            'role_label' => $roleLabel,
            'is_self' => $isSelf,
            'followers_count' => $hasFollows ? $user->followers()->count() : 0,
            'following_count' => $hasFollows ? $user->following()->count() : 0,
            'can_follow' => Auth::check() && ! $isSelf && $hasFollows,
            'is_following' => $isFollowing,
        ]);
    }
}
