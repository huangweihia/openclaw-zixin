<?php

namespace App\Support;

use Illuminate\Support\Str;

class AdminPermissionModuleZh
{
    public static function label(?string $module): string
    {
        if ($module === null || $module === '') {
            return '未分组';
        }
        $map = config('filament_admin_permission_module_zh', []);

        return $map[$module] ?? Str::headline(str_replace('-', ' ', $module));
    }

    public static function actionLabel(?string $action): string
    {
        return match ($action) {
            'read' => '只读',
            'write' => '写入',
            'all' => '全部',
            null, '' => '—',
            default => (string) $action,
        };
    }
}
