<?php

namespace App\Support;

use App\Models\AdminNavItem;
use Illuminate\Support\Facades\Cache;

class AdminNavRegistry
{
    public const CACHE_KEY = 'admin_nav_items_by_menu_key';

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, AdminNavItem>
     */
    public static function itemsByMenuKey(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return AdminNavItem::query()
                ->with('section')
                ->get()
                ->keyBy('menu_key')
                ->all();
        });
    }

    public static function item(?string $menuKey): ?AdminNavItem
    {
        if ($menuKey === null || $menuKey === '') {
            return null;
        }

        return self::itemsByMenuKey()[$menuKey] ?? null;
    }

    public static function navigationLabel(string $menuKey): ?string
    {
        $item = self::item($menuKey);

        return $item?->label;
    }

    public static function navigationGroupTitle(string $menuKey): ?string
    {
        $item = self::item($menuKey);

        return $item?->section?->title;
    }

    public static function navigationSort(string $menuKey): ?int
    {
        $item = self::item($menuKey);

        return $item !== null ? (int) $item->sort_order : null;
    }
}
