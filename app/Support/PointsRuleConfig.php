<?php

namespace App\Support;

use App\Models\SiteSetting;

final class PointsRuleConfig
{
    public static function loginDaily(): int
    {
        return self::intValue('points_rule_login_daily', (int) config('points_rewards.login_daily', 5));
    }

    public static function postApproved(): int
    {
        return self::intValue('points_rule_post_approved', (int) config('points_rewards.post_approved', 20));
    }

    public static function postLikedAuthor(): int
    {
        return self::intValue('points_rule_post_liked_author', (int) config('points_rewards.post_liked_author', 2));
    }

    public static function postFavoritedAuthor(): int
    {
        return self::intValue('points_rule_post_favorited_author', (int) config('points_rewards.post_favorited_author', 3));
    }

    public static function postCommentedAuthor(): int
    {
        return self::intValue('points_rule_post_commented_author', (int) config('points_rewards.post_commented_author', 2));
    }

    public static function boostCost(): int
    {
        return max(1, self::intValue('points_rule_boost_cost', (int) config('boost.points_per_boost', 100)));
    }

    public static function boostWindowHours(): int
    {
        return max(1, self::intValue('points_rule_boost_window_hours', (int) config('boost.window_hours', 72)));
    }

    public static function boostRandomNotifyUsers(): int
    {
        return max(0, self::intValue('points_rule_boost_random_notify_users', (int) config('boost.random_notify_users', 15)));
    }

    public static function boostDailyCapPerPost(): int
    {
        return max(1, self::intValue('points_rule_boost_daily_cap_per_post', (int) config('boost.max_boosts_per_actor_per_post_per_day', 3)));
    }

    private static function intValue(string $key, int $fallback): int
    {
        $raw = SiteSetting::getValue($key);
        if ($raw === null || trim($raw) === '' || ! is_numeric($raw)) {
            return $fallback;
        }

        return (int) $raw;
    }
}
