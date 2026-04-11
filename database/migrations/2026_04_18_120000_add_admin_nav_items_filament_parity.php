<?php

use App\Support\AdminNavRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 与《后台Vue与Filament差异说明》对齐：为未入表的 menu_key 补导航项，便于 RBAC + 侧栏与 filament_admin_menu_map 一致。
     */
    public function up(): void
    {
        if (! Schema::hasTable('admin_nav_items') || ! Schema::hasTable('admin_nav_sections')) {
            return;
        }

        $now = now();

        $sectionModeration = DB::table('admin_nav_sections')->where('title', '审核与社区')->value('id');
        $sectionTrade = DB::table('admin_nav_sections')->where('title', '交易 · 用户 · 售后')->value('id');
        $sectionOps = DB::table('admin_nav_sections')->where('title', '运营与触达')->value('id');
        $sectionSystem = DB::table('admin_nav_sections')->where('title', '系统')->value('id');

        if ($sectionModeration && ! DB::table('admin_nav_items')->where('menu_key', 'moderation-hub')->exists()) {
            DB::table('admin_nav_items')->where('menu_key', 'moderation')->update(['sort_order' => 10]);
            DB::table('admin_nav_items')->insert([
                'admin_nav_section_id' => $sectionModeration,
                'menu_key' => 'moderation-hub',
                'label' => '审核工作台',
                'path' => null,
                'external_url' => null,
                'icon' => 'ep:Monitor',
                'perm_key' => 'admin:moderation:read',
                'sort_order' => 0,
                'match_exact' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $newItems = [
            [$sectionSystem, 'admin-permissions', '权限字典', 'admin:roles:read', 15],
            [$sectionSystem, 'admin-users', '后台用户档案', 'admin:roles:read', 16],
            [$sectionSystem, 'admin-nav-sections', '导航分区', 'admin:menus:read', 17],
            [$sectionOps, 'user-skins', '用户皮肤', 'admin:skin-configs:read', 85],
            [$sectionTrade, 'svip-subscriptions', 'SVIP 内容订阅', 'admin:svip-custom-subscriptions:read', 25],
        ];

        foreach ($newItems as [$sectionId, $key, $label, $perm, $sort]) {
            if (! $sectionId) {
                continue;
            }
            if (DB::table('admin_nav_items')->where('menu_key', $key)->exists()) {
                continue;
            }
            DB::table('admin_nav_items')->insert([
                'admin_nav_section_id' => $sectionId,
                'menu_key' => $key,
                'label' => $label,
                'path' => null,
                'external_url' => null,
                'icon' => null,
                'perm_key' => $perm,
                'sort_order' => $sort,
                'match_exact' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        AdminNavRegistry::forgetCache();
    }

    public function down(): void
    {
        if (! Schema::hasTable('admin_nav_items')) {
            return;
        }

        DB::table('admin_nav_items')->whereIn('menu_key', [
            'moderation-hub',
            'admin-permissions',
            'admin-users',
            'admin-nav-sections',
            'user-skins',
            'svip-subscriptions',
        ])->delete();

        DB::table('admin_nav_items')->where('menu_key', 'moderation')->update(['sort_order' => 0]);

        AdminNavRegistry::forgetCache();
    }
};
