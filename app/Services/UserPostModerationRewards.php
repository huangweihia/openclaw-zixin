<?php

namespace App\Services;

use App\Models\UserPost;
use App\Support\PointsRuleConfig;

/**
 * 投稿审核通过：积分 + 热度（Web / API 审核共用）。
 */
final class UserPostModerationRewards
{
    public static function onApproved(UserPost $post): void
    {
        $post->loadMissing('author');
        $author = $post->author;
        if ($author) {
            $pts = PointsRuleConfig::postApproved();
            if ($pts > 0) {
                PointsService::earn(
                    $author,
                    $pts,
                    'post_approve',
                    '投稿审核通过奖励',
                    $post->getMorphClass(),
                    (int) $post->id
                );
            }
        }

        $heat = (int) config('heat.post_approved', 0);
        if ($heat > 0) {
            UserPostHeatService::increment($post, $heat, 'approved');
        }
    }
}
