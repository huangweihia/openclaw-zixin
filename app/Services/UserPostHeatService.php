<?php

namespace App\Services;

use App\Models\UserPost;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * 投稿热度（heat_score）累计；与加热 boost_weight 分离。
 */
final class UserPostHeatService
{
    public static function increment(UserPost $post, int $delta, string $reason = ''): void
    {
        if ($delta <= 0 || ! Schema::hasColumn('user_posts', 'heat_score')) {
            return;
        }

        UserPost::query()->whereKey($post->id)->increment('heat_score', $delta);
    }

    /**
     * 登录用户对投稿详情：每自然日每帖计一次浏览热度。
     */
    public static function recordAuthenticatedView(UserPost $post, int $userId): void
    {
        if (! Schema::hasColumn('user_posts', 'heat_score')) {
            return;
        }

        $day = now()->toDateString();
        $key = "heat:post_view:{$post->id}:{$userId}:{$day}";
        if (! Cache::add($key, 1, now()->endOfDay())) {
            return;
        }

        $delta = (int) config('heat.view_once_per_day', 1);
        self::increment($post, $delta, 'view');
    }
}
