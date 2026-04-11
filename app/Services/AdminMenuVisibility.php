<?php

namespace App\Services;

use App\Models\User;

/**
 * 角色「菜单白名单」：与权限取交集由前端完成；此处只返回需额外收窄的 key 集合。
 */
final class AdminMenuVisibility
{
    /**
     * @return array<int, string>|null null 表示不按角色白名单收窄（仅按权限）
     */
    public static function menuKeyWhitelistForUser(User $user): ?array
    {
        if ($user->adminUser?->is_super) {
            return null;
        }

        $roles = $user->adminRoles()->with('menuItems')->get();
        $anyWhitelist = false;
        $union = [];

        foreach ($roles as $role) {
            if (($role->menu_mode ?? 'inherit') !== 'whitelist') {
                continue;
            }
            $anyWhitelist = true;
            foreach ($role->menuItems as $item) {
                $k = (string) $item->menu_key;
                if ($k !== '') {
                    $union[] = $k;
                }
            }
        }

        if (! $anyWhitelist) {
            return null;
        }

        $union = array_values(array_unique($union));
        if ($union === []) {
            return null;
        }

        return $union;
    }
}
