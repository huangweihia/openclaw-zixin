<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Support\AdminNavRegistry;
use Filament\Resources\Resource;

abstract class BaseAdminResource extends Resource
{
    protected static function adminMenuKey(): ?string
    {
        return config('filament_admin_menu_map.'.static::class);
    }

    public static function getNavigationLabel(): string
    {
        $mk = static::adminMenuKey();
        if ($mk !== null) {
            $label = AdminNavRegistry::navigationLabel($mk);
            if ($label !== null && $label !== '') {
                return $label;
            }
        }

        return static::getModelLabel();
    }

    public static function getNavigationGroup(): ?string
    {
        $mk = static::adminMenuKey();
        if ($mk !== null) {
            $title = AdminNavRegistry::navigationGroupTitle($mk);
            if ($title !== null && $title !== '') {
                return $title;
            }
        }

        return static::$navigationGroup;
    }

    public static function getNavigationSort(): ?int
    {
        $mk = static::adminMenuKey();
        if ($mk !== null) {
            $sort = AdminNavRegistry::navigationSort($mk);
            if ($sort !== null) {
                return $sort;
            }
        }

        return static::$navigationSort;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $mk = static::adminMenuKey();
        if ($mk === null) {
            return true;
        }

        $item = AdminNavRegistry::item($mk);
        if ($item !== null && ! $item->is_active) {
            return false;
        }

        $user = auth()->user();
        if (! $user instanceof User) {
            return false;
        }

        return $user->allowsAdminMenuKey($mk);
    }

    public static function canViewAny(): bool
    {
        $u = auth()->user();
        if (! $u instanceof User || $u->role !== 'admin' || $u->is_banned) {
            return false;
        }

        $mk = static::adminMenuKey();
        if ($mk === null) {
            return true;
        }

        return $u->allowsAdminMenuKey($mk);
    }
}
