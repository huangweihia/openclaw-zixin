<?php

namespace App\Support;

use App\Models\AdminNavItem;
use App\Models\AdminNavSection;
use Illuminate\Support\Facades\Schema;

/**
 * 菜单目录：数据来自 admin_nav_sections / admin_nav_items；无表时回退静态列表（测试/未迁移环境）。
 */
final class AdminMenuCatalog
{
    /**
     * @return array<int, array{key: string, perm: string, section: string, label: string, path?: string|null, match_exact?: bool}>
     */
    public static function items(): array
    {
        if (! Schema::hasTable('admin_nav_items') || ! Schema::hasTable('admin_nav_sections')) {
            return self::legacyItems();
        }

        $sections = AdminNavSection::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->with(['items' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
            ->get();

        $out = [];
        foreach ($sections as $section) {
            foreach ($section->items as $item) {
                $out[] = [
                    'key' => $item->menu_key,
                    'perm' => $item->perm_key,
                    'section' => $section->title,
                    'label' => $item->label,
                    'path' => $item->path,
                    'match_exact' => (bool) $item->match_exact,
                ];
            }
        }

        return $out;
    }

    /**
     * @return array<string, string> menu_key => perm
     */
    public static function keyToPermMap(): array
    {
        $map = [];
        foreach (self::items() as $row) {
            $map[$row['key']] = $row['perm'];
        }

        return $map;
    }

    /**
     * @return array<int, string>
     */
    public static function validKeys(): array
    {
        if (Schema::hasTable('admin_nav_items')) {
            return AdminNavItem::query()->orderBy('menu_key')->pluck('menu_key')->all();
        }

        return array_values(array_unique(array_map(fn (array $r) => $r['key'], self::legacyItems())));
    }

    /**
     * @return array<int, array{section: string, items: array<int, array{key: string, label: string, perm: string}>}>
     */
    public static function forEditor(): array
    {
        $bySection = [];
        foreach (self::items() as $row) {
            $sec = $row['section'];
            if (! isset($bySection[$sec])) {
                $bySection[$sec] = [
                    'section' => $sec,
                    'items' => [],
                ];
            }
            $bySection[$sec]['items'][] = [
                'key' => $row['key'],
                'label' => $row['label'],
                'perm' => $row['perm'],
            ];
        }

        return array_values($bySection);
    }

    /**
     * @param  array<int, string>  $permKeys
     * @return array<int, string>
     */
    public static function keysAllowedByPermissions(array $permKeys): array
    {
        $permKeys = array_values(array_filter(array_map('strval', $permKeys)));
        $hasStar = in_array('*', $permKeys, true);
        $allowed = [];
        foreach (self::items() as $row) {
            if ($hasStar) {
                $allowed[] = $row['key'];

                continue;
            }
            if (self::permGranted($row['perm'], $permKeys)) {
                $allowed[] = $row['key'];
            }
        }

        return array_values(array_unique($allowed));
    }

    /**
     * @param  array<int, string>  $permKeys
     */
    public static function permGranted(string $permKey, array $permKeys): bool
    {
        if (in_array('*', $permKeys, true) || in_array($permKey, $permKeys, true)) {
            return true;
        }
        $parts = explode(':', $permKey);
        $module = $parts[1] ?? null;
        if ($module) {
            if (in_array("admin:{$module}:*", $permKeys, true) || in_array("admin:{$module}", $permKeys, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array{key: string, perm: string, section: string, label: string, path?: string|null, match_exact?: bool}>
     */
    private static function legacyItems(): array
    {
        return [
            ['key' => 'dashboard', 'perm' => 'admin:dashboard:read', 'section' => '总览', 'label' => '仪表盘', 'match_exact' => true],
            ['key' => 'articles', 'perm' => 'admin:articles:read', 'section' => '内容中心', 'label' => '文章管理'],
            ['key' => 'projects', 'perm' => 'admin:projects:read', 'section' => '内容中心', 'label' => '项目管理'],
            ['key' => 'categories', 'perm' => 'admin:categories:read', 'section' => '内容中心', 'label' => '分类管理'],
            ['key' => 'premium-resources', 'perm' => 'admin:premium-resources:read', 'section' => '内容中心', 'label' => '会员资源'],
            ['key' => 'side-hustle-cases', 'perm' => 'admin:side-hustle-cases:read', 'section' => '内容中心', 'label' => '副业案例'],
            ['key' => 'private-traffic-sops', 'perm' => 'admin:private-traffic-sops:read', 'section' => '内容中心', 'label' => '私域 SOP'],
            ['key' => 'ai-tool-monetization', 'perm' => 'admin:ai-tool-monetization:read', 'section' => '内容中心', 'label' => 'AI 工具变现'],
            ['key' => 'moderation', 'perm' => 'admin:moderation:read', 'section' => '审核与社区', 'label' => '投稿审核'],
            ['key' => 'comments', 'perm' => 'admin:comments:read', 'section' => '审核与社区', 'label' => '评论管理'],
            ['key' => 'orders', 'perm' => 'admin:orders:read', 'section' => '交易 · 用户 · 售后', 'label' => '订单管理'],
            ['key' => 'subscriptions', 'perm' => 'admin:subscriptions:read', 'section' => '交易 · 用户 · 售后', 'label' => '会员订阅'],
            ['key' => 'svip-custom-subscriptions', 'perm' => 'admin:svip-custom-subscriptions:read', 'section' => '交易 · 用户 · 售后', 'label' => 'SVIP 定制'],
            ['key' => 'view-histories', 'perm' => 'admin:view-histories:read', 'section' => '交易 · 用户 · 售后', 'label' => '浏览历史'],
            ['key' => 'users', 'perm' => 'admin:users:read', 'section' => '交易 · 用户 · 售后', 'label' => '用户管理'],
            ['key' => 'points-ledger', 'perm' => 'admin:points-ledger:read', 'section' => '交易 · 用户 · 售后', 'label' => '积分流水'],
            ['key' => 'refund-requests', 'perm' => 'admin:refund-requests:read', 'section' => '交易 · 用户 · 售后', 'label' => '退款申请'],
            ['key' => 'invoice-requests', 'perm' => 'admin:invoice-requests:read', 'section' => '交易 · 用户 · 售后', 'label' => '发票申请'],
            ['key' => 'comment-reports', 'perm' => 'admin:comment-reports:read', 'section' => '交易 · 用户 · 售后', 'label' => '评论举报'],
            ['key' => 'email-templates', 'perm' => 'admin:email-templates:read', 'section' => '运营与触达', 'label' => '邮件模板'],
            ['key' => 'email-logs', 'perm' => 'admin:email-logs:read', 'section' => '运营与触达', 'label' => '邮件记录'],
            ['key' => 'email-subscriptions', 'perm' => 'admin:email-subscriptions:read', 'section' => '运营与触达', 'label' => '邮箱订阅'],
            ['key' => 'site-testimonials', 'perm' => 'admin:site-testimonials:read', 'section' => '运营与触达', 'label' => '首页评价'],
            ['key' => 'announcements', 'perm' => 'admin:announcements:read', 'section' => '运营与触达', 'label' => '公告管理'],
            ['key' => 'system-notifications', 'perm' => 'admin:system-notifications:read', 'section' => '运营与触达', 'label' => '系统通知'],
            ['key' => 'push-notifications', 'perm' => 'admin:push-notifications:read', 'section' => '运营与触达', 'label' => '站内推送'],
            ['key' => 'skin-configs', 'perm' => 'admin:skin-configs:read', 'section' => '运营与触达', 'label' => '皮肤主题'],
            ['key' => 'ad-slots', 'perm' => 'admin:ad-slots:read', 'section' => '运营与触达', 'label' => '广告位'],
            ['key' => 'openclaw-task-logs', 'perm' => 'admin:openclaw-task-logs:read', 'section' => '运营与触达', 'label' => '任务日志'],
            ['key' => 'personality-quiz', 'perm' => 'admin:settings:read', 'section' => '小游戏', 'label' => 'SBTI 配置'],
            ['key' => 'settings', 'perm' => 'admin:settings:read', 'section' => '系统', 'label' => '系统与站点'],
            ['key' => 'email-settings', 'perm' => 'admin:email-settings:read', 'section' => '系统', 'label' => '邮件 SMTP 配置'],
            ['key' => 'shared-components', 'perm' => 'admin:shared-components:read', 'section' => '系统', 'label' => '公共组件'],
            ['key' => 'audit-logs', 'perm' => 'admin:audit-logs:read', 'section' => '系统', 'label' => '操作审计'],
            ['key' => 'publish-audits', 'perm' => 'admin:publish-audits:read', 'section' => '系统', 'label' => '发布审核记录'],
            ['key' => 'admin-roles', 'perm' => 'admin:roles:read', 'section' => '系统', 'label' => '角色与菜单'],
            ['key' => 'nav-menus', 'perm' => 'admin:menus:read', 'section' => '系统', 'label' => '菜单与导航'],
        ];
    }
}
