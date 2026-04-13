<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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

        $recentPosts = collect();
        if (Schema::hasTable('user_posts')) {
            $recentPosts = $user->posts()
                ->where('status', 'approved')
                ->whereIn('visibility', ['public', 'vip'])
                ->latest('id')
                ->limit(3)
                ->get(['id', 'title', 'content', 'updated_at'])
                ->map(function ($post) {
                    return [
                        'id' => (int) $post->id,
                        'title' => (string) ($post->title ?? ''),
                        'summary' => Str::limit(trim(strip_tags((string) ($post->content ?? ''))), 90),
                        'url' => route('posts.show', $post),
                        'updated_at' => $post->updated_at?->format('Y-m-d'),
                    ];
                });
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar,
            'role_label' => $roleLabel,
            'is_self' => $isSelf,
            'followers_count' => $hasFollows ? $user->followers()->count() : 0,
            'following_count' => $hasFollows ? $user->following()->count() : 0,
            'can_follow' => Auth::check() && ! $isSelf && $hasFollows,
            'is_following' => $isFollowing,
            'recent_posts' => $recentPosts,
        ]);
    }
}
