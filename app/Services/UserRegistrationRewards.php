<?php

namespace App\Services;

use App\Models\SiteSetting;
use App\Models\User;

/**
 * 与后台「站点与系统设置 → 注册与会员」一致：新用户赠送角色/天数、注册积分。
 * 邮箱注册与微信小程序首次注册共用。
 */
final class UserRegistrationRewards
{
    public static function applyRegisterGift(User $user): void
    {
        if (SiteSetting::getValue('register_gift_enabled', '0') !== '1') {
            return;
        }
        $days = max(0, (int) SiteSetting::getValue('register_gift_days', '0'));
        $role = (string) SiteSetting::getValue('register_gift_role', 'vip');
        if ($days <= 0 || ! in_array($role, ['vip', 'svip'], true)) {
            return;
        }
        $user->forceFill([
            'role' => $role,
            'subscription_ends_at' => now()->addDays($days),
        ])->save();
    }

    public static function applyRegisterPoints(User $user): void
    {
        $bonus = max(0, (int) SiteSetting::getValue('register_points_bonus', '0'));
        if ($bonus <= 0) {
            return;
        }
        PointsService::earn($user, $bonus, 'register', '新用户注册奖励');
    }
}
