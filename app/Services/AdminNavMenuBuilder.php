<?php

namespace App\Services;

use App\Models\AdminNavSection;
use App\Models\User;
use App\Support\AdminMenuCatalog;

final class AdminNavMenuBuilder
{
    /**
     * 侧边栏菜单（已按权限 + 角色菜单白名单过滤）。
     *
     * @return array<int, array{section: string, section_id: int, items: array<int, array<string, mixed>>}>
     */
    public function sidebarForUser(User $user): array
    {
        $perms = $user->adminPermissions();
        if ($user->adminUser?->is_super) {
            $perms = ['*'];
        }

        $whitelist = AdminMenuVisibility::menuKeyWhitelistForUser($user);
        if ($user->adminUser?->is_super) {
            $whitelist = null;
        }

        $sections = AdminNavSection::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with(['items' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order')->orderBy('id');
            }])
            ->get();

        $out = [];
        foreach ($sections as $section) {
            $items = [];
            foreach ($section->items as $item) {
                if (! AdminMenuCatalog::permGranted($item->perm_key, $perms)) {
                    continue;
                }
                if ($whitelist !== null && ! in_array($item->menu_key, $whitelist, true)) {
                    continue;
                }

                $external = $item->external_url ? trim((string) $item->external_url) : '';
                $path = $item->path !== null ? trim((string) $item->path) : '';
                $to = $external !== '' ? '__ext:'.$item->id : ($path !== '' ? $path : '/');

                $items[] = [
                    'key' => $item->menu_key,
                    'to' => $to,
                    'label' => $item->label,
                    'icon' => $item->icon,
                    'perm' => $item->perm_key,
                    'match' => $item->match_exact ? 'exact' : null,
                    'external_url' => $external !== '' ? $external : null,
                ];
            }

            if ($items !== []) {
                $out[] = [
                    'section' => $section->title,
                    'section_id' => (int) $section->id,
                    'items' => $items,
                ];
            }
        }

        return $out;
    }
}
