<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('admin_nav_sections') || ! Schema::hasTable('admin_nav_items')) {
            return;
        }

        $now = now();

        $sections = [
            ['title' => '运营看板', 'sort_order' => 0],
            ['title' => '内容生产', 'sort_order' => 10],
            ['title' => '社区互动', 'sort_order' => 20],
            ['title' => '用户与资产', 'sort_order' => 30],
            ['title' => '交易与售后', 'sort_order' => 40],
            ['title' => '触达与营销', 'sort_order' => 50],
            ['title' => '平台配置', 'sort_order' => 60],
            ['title' => '权限与导航', 'sort_order' => 70],
            ['title' => '小游戏', 'sort_order' => 80],
        ];

        $sectionIds = [];
        foreach ($sections as $s) {
            $id = DB::table('admin_nav_sections')->where('title', $s['title'])->value('id');
            if (! $id) {
                $id = DB::table('admin_nav_sections')->insertGetId([
                    'title' => $s['title'],
                    'sort_order' => $s['sort_order'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('admin_nav_sections')
                    ->where('id', $id)
                    ->update([
                        'sort_order' => $s['sort_order'],
                        'is_active' => true,
                        'updated_at' => $now,
                    ]);
            }
            $sectionIds[$s['title']] = (int) $id;
        }

        $map = [
            // 运营看板
            'dashboard' => ['运营看板', 0],
            'openclaw-task-logs' => ['运营看板', 10],

            // 内容生产
            'articles' => ['内容生产', 0],
            'projects' => ['内容生产', 10],
            'side-hustle-cases' => ['内容生产', 20],
            'private-traffic-sops' => ['内容生产', 30],
            'ai-tool-monetization' => ['内容生产', 40],
            'categories' => ['内容生产', 50],
            'premium-resources' => ['内容生产', 60],

            // 社区互动
            'moderation' => ['社区互动', 0],
            'comments' => ['社区互动', 10],
            'comment-reports' => ['社区互动', 20],
            'content-boosts' => ['社区互动', 30],
            'system-notifications' => ['社区互动', 40],

            // 用户与资产
            'users' => ['用户与资产', 0],
            'view-histories' => ['用户与资产', 10],
            'points-ledger' => ['用户与资产', 20],
            'point-packages' => ['用户与资产', 30],
            'subscriptions' => ['用户与资产', 40],
            'svip-custom-subscriptions' => ['用户与资产', 50],
            'svip-subscriptions' => ['用户与资产', 60],

            // 交易与售后
            'orders' => ['交易与售后', 0],
            'refund-requests' => ['交易与售后', 10],
            'invoice-requests' => ['交易与售后', 20],

            // 触达与营销
            'announcements' => ['触达与营销', 0],
            'push-notifications' => ['触达与营销', 10],
            'email-templates' => ['触达与营销', 20],
            'email-logs' => ['触达与营销', 30],
            'email-subscriptions' => ['触达与营销', 40],
            'site-testimonials' => ['触达与营销', 50],
            'ad-slots' => ['触达与营销', 60],
            'skin-configs' => ['触达与营销', 70],

            // 平台配置
            'settings' => ['平台配置', 0],
            'email-settings' => ['平台配置', 10],
            'shared-components' => ['平台配置', 20],
            'publish-audits' => ['平台配置', 30],
            'audit-logs' => ['平台配置', 40],

            // 权限与导航
            'admin-roles' => ['权限与导航', 0],
            'nav-menus' => ['权限与导航', 10],
            'admin-users' => ['权限与导航', 20],
            'admin-permissions' => ['权限与导航', 30],
            'admin-nav-sections' => ['权限与导航', 40],

            // 小游戏
            'personality-quiz' => ['小游戏', 0],
        ];

        foreach ($map as $menuKey => [$sectionTitle, $sort]) {
            $sectionId = $sectionIds[$sectionTitle] ?? null;
            if (! $sectionId) {
                continue;
            }

            DB::table('admin_nav_items')
                ->where('menu_key', $menuKey)
                ->update([
                    'admin_nav_section_id' => $sectionId,
                    'sort_order' => $sort,
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        // 回滚时不删除菜单，仅恢复为旧分组标题与顺序（与初始迁移一致）。
        if (! Schema::hasTable('admin_nav_sections') || ! Schema::hasTable('admin_nav_items')) {
            return;
        }

        $now = now();
        $legacySections = [
            ['title' => '总览', 'sort_order' => 0],
            ['title' => '内容中心', 'sort_order' => 10],
            ['title' => '审核与社区', 'sort_order' => 20],
            ['title' => '交易 · 用户 · 售后', 'sort_order' => 30],
            ['title' => '运营与触达', 'sort_order' => 40],
            ['title' => '小游戏', 'sort_order' => 50],
            ['title' => '系统', 'sort_order' => 60],
        ];

        $legacyIds = [];
        foreach ($legacySections as $s) {
            $id = DB::table('admin_nav_sections')->where('title', $s['title'])->value('id');
            if (! $id) {
                $id = DB::table('admin_nav_sections')->insertGetId([
                    'title' => $s['title'],
                    'sort_order' => $s['sort_order'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('admin_nav_sections')->where('id', $id)->update([
                    'sort_order' => $s['sort_order'],
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
            }
            $legacyIds[$s['title']] = (int) $id;
        }

        $legacyMap = [
            'dashboard' => ['总览', 0],
            'articles' => ['内容中心', 0],
            'projects' => ['内容中心', 10],
            'categories' => ['内容中心', 20],
            'premium-resources' => ['内容中心', 30],
            'side-hustle-cases' => ['内容中心', 40],
            'private-traffic-sops' => ['内容中心', 50],
            'ai-tool-monetization' => ['内容中心', 60],
            'moderation' => ['审核与社区', 0],
            'comments' => ['审核与社区', 10],
            'orders' => ['交易 · 用户 · 售后', 0],
            'subscriptions' => ['交易 · 用户 · 售后', 10],
            'svip-custom-subscriptions' => ['交易 · 用户 · 售后', 20],
            'view-histories' => ['交易 · 用户 · 售后', 30],
            'users' => ['交易 · 用户 · 售后', 40],
            'points-ledger' => ['交易 · 用户 · 售后', 50],
            'point-packages' => ['交易 · 用户 · 售后', 55],
            'content-boosts' => ['交易 · 用户 · 售后', 56],
            'refund-requests' => ['交易 · 用户 · 售后', 60],
            'invoice-requests' => ['交易 · 用户 · 售后', 70],
            'comment-reports' => ['交易 · 用户 · 售后', 80],
            'email-templates' => ['运营与触达', 0],
            'email-logs' => ['运营与触达', 10],
            'email-subscriptions' => ['运营与触达', 20],
            'site-testimonials' => ['运营与触达', 30],
            'announcements' => ['运营与触达', 40],
            'system-notifications' => ['运营与触达', 50],
            'push-notifications' => ['运营与触达', 60],
            'skin-configs' => ['运营与触达', 70],
            'ad-slots' => ['运营与触达', 80],
            'openclaw-task-logs' => ['运营与触达', 90],
            'personality-quiz' => ['小游戏', 0],
            'settings' => ['系统', 0],
            'email-settings' => ['系统', 10],
            'shared-components' => ['系统', 20],
            'audit-logs' => ['系统', 30],
            'publish-audits' => ['系统', 40],
            'admin-roles' => ['系统', 50],
            'nav-menus' => ['系统', 60],
            'admin-users' => ['系统', 70],
            'admin-permissions' => ['系统', 80],
            'admin-nav-sections' => ['系统', 90],
        ];

        foreach ($legacyMap as $menuKey => [$sectionTitle, $sort]) {
            $sectionId = $legacyIds[$sectionTitle] ?? null;
            if (! $sectionId) {
                continue;
            }

            DB::table('admin_nav_items')
                ->where('menu_key', $menuKey)
                ->update([
                    'admin_nav_section_id' => $sectionId,
                    'sort_order' => $sort,
                    'updated_at' => $now,
                ]);
        }
    }
};
