<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('admin_nav_sections')) {
            Schema::create('admin_nav_sections', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('admin_nav_items')) {
            Schema::create('admin_nav_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_nav_section_id')->constrained('admin_nav_sections')->cascadeOnDelete();
                $table->string('menu_key', 128)->unique();
                $table->string('label');
                $table->string('path', 512)->nullable();
                $table->string('external_url', 1024)->nullable();
                $table->string('icon', 64)->nullable();
                $table->string('perm_key', 128);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('match_exact')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['admin_nav_section_id', 'sort_order'], 'admin_nav_items_section_sort_idx');
            });
        }

        if (DB::table('admin_nav_items')->exists()) {
            return;
        }

        $now = now();
        $sections = [
            ['title' => '总览', 'sort_order' => 0],
            ['title' => '内容中心', 'sort_order' => 10],
            ['title' => '审核与社区', 'sort_order' => 20],
            ['title' => '交易 · 用户 · 售后', 'sort_order' => 30],
            ['title' => '运营与触达', 'sort_order' => 40],
            ['title' => '小游戏', 'sort_order' => 50],
            ['title' => '系统', 'sort_order' => 60],
        ];

        $sectionIds = [];
        foreach ($sections as $s) {
            $sectionIds[] = DB::table('admin_nav_sections')->insertGetId(array_merge($s, [
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $items = [
            [$sectionIds[0], 'dashboard', '仪表盘', '/', null, 'ep:Odometer', 'admin:dashboard:read', 0, true],
            [$sectionIds[1], 'articles', '文章管理', '/articles', null, 'ep:Document', 'admin:articles:read', 0, false],
            [$sectionIds[1], 'projects', '项目管理', '/projects', null, 'ep:FolderOpened', 'admin:projects:read', 10, false],
            [$sectionIds[1], 'categories', '分类管理', '/categories', null, 'ep:Files', 'admin:categories:read', 20, false],
            [$sectionIds[1], 'premium-resources', '会员资源', '/premium-resources', null, 'ep:Medal', 'admin:premium-resources:read', 30, false],
            [$sectionIds[1], 'side-hustle-cases', '副业案例', '/side-hustle-cases', null, 'ep:Briefcase', 'admin:side-hustle-cases:read', 40, false],
            [$sectionIds[1], 'private-traffic-sops', '私域 SOP', '/private-traffic-sops', null, 'ep:Iphone', 'admin:private-traffic-sops:read', 50, false],
            [$sectionIds[1], 'ai-tool-monetization', 'AI 工具变现', '/ai-tool-monetization', null, 'ep:Cpu', 'admin:ai-tool-monetization:read', 60, false],
            [$sectionIds[2], 'moderation', '投稿审核', '/moderation', null, 'ep:CircleCheck', 'admin:moderation:read', 0, false],
            [$sectionIds[2], 'comments', '评论管理', '/comments', null, 'ep:ChatDotRound', 'admin:comments:read', 10, false],
            [$sectionIds[3], 'orders', '订单管理', '/orders', null, 'ep:CreditCard', 'admin:orders:read', 0, false],
            [$sectionIds[3], 'subscriptions', '会员订阅', '/subscriptions', null, 'ep:StarFilled', 'admin:subscriptions:read', 10, false],
            [$sectionIds[3], 'svip-custom-subscriptions', 'SVIP 定制', '/svip-custom-subscriptions', null, 'ep:Grid', 'admin:svip-custom-subscriptions:read', 20, false],
            [$sectionIds[3], 'view-histories', '浏览历史', '/view-histories', null, 'ep:View', 'admin:view-histories:read', 30, false],
            [$sectionIds[3], 'users', '用户管理', '/users', null, 'ep:User', 'admin:users:read', 40, false],
            [$sectionIds[3], 'points-ledger', '积分流水', '/points-ledger', null, 'ep:Coin', 'admin:points-ledger:read', 50, false],
            [$sectionIds[3], 'refund-requests', '退款申请', '/refund-requests', null, 'ep:RefreshLeft', 'admin:refund-requests:read', 60, false],
            [$sectionIds[3], 'invoice-requests', '发票申请', '/invoice-requests', null, 'ep:Tickets', 'admin:invoice-requests:read', 70, false],
            [$sectionIds[3], 'comment-reports', '评论举报', '/comment-reports', null, 'ep:WarningFilled', 'admin:comment-reports:read', 80, false],
            [$sectionIds[4], 'email-templates', '邮件模板', '/email-templates', null, 'ep:Message', 'admin:email-templates:read', 0, false],
            [$sectionIds[4], 'email-logs', '邮件记录', '/email-logs', null, 'ep:Promotion', 'admin:email-logs:read', 10, false],
            [$sectionIds[4], 'email-subscriptions', '邮箱订阅', '/email-subscriptions', null, 'ep:Postcard', 'admin:email-subscriptions:read', 20, false],
            [$sectionIds[4], 'site-testimonials', '首页评价', '/site-testimonials', null, 'ep:Star', 'admin:site-testimonials:read', 30, false],
            [$sectionIds[4], 'announcements', '公告管理', '/announcements', null, 'ep:Bell', 'admin:announcements:read', 40, false],
            [$sectionIds[4], 'system-notifications', '系统通知', '/system-notifications', null, 'ep:Notification', 'admin:system-notifications:read', 50, false],
            [$sectionIds[4], 'push-notifications', '站内推送', '/push-notifications', null, 'ep:Iphone', 'admin:push-notifications:read', 60, false],
            [$sectionIds[4], 'skin-configs', '皮肤主题', '/skin-configs', null, 'ep:Brush', 'admin:skin-configs:read', 70, false],
            [$sectionIds[4], 'ad-slots', '广告位', '/ad-slots', null, 'ep:Microphone', 'admin:ad-slots:read', 80, false],
            [$sectionIds[4], 'openclaw-task-logs', '任务日志', '/openclaw-task-logs', null, 'ep:List', 'admin:openclaw-task-logs:read', 90, false],
            [$sectionIds[5], 'personality-quiz', 'SBTI 配置', '/personality-quiz', null, 'ep:Pointer', 'admin:settings:read', 0, false],
            [$sectionIds[6], 'settings', '系统与站点', '/settings', null, 'ep:Setting', 'admin:settings:read', 0, false],
            [$sectionIds[6], 'email-settings', '邮件 SMTP 配置', '/email-settings', null, 'ep:Tools', 'admin:email-settings:read', 10, false],
            [$sectionIds[6], 'shared-components', '公共组件', '/shared-components', null, 'ep:Grid', 'admin:shared-components:read', 20, false],
            [$sectionIds[6], 'audit-logs', '操作审计', '/audit-logs', null, 'ep:DocumentCopy', 'admin:audit-logs:read', 30, false],
            [$sectionIds[6], 'publish-audits', '发布审核记录', '/publish-audits', null, 'ep:Document', 'admin:publish-audits:read', 40, false],
            [$sectionIds[6], 'admin-roles', '角色与菜单', '/admin-roles', null, 'ep:Lock', 'admin:roles:read', 50, false],
            [$sectionIds[6], 'nav-menus', '菜单与导航', '/nav-menus', null, 'ep:Menu', 'admin:menus:read', 60, false],
        ];

        foreach ($items as $row) {
            DB::table('admin_nav_items')->insert([
                'admin_nav_section_id' => $row[0],
                'menu_key' => $row[1],
                'label' => $row[2],
                'path' => $row[3],
                'external_url' => $row[4],
                'icon' => $row[5],
                'perm_key' => $row[6],
                'sort_order' => $row[7],
                'match_exact' => $row[8],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_nav_items');
        Schema::dropIfExists('admin_nav_sections');
    }
};
