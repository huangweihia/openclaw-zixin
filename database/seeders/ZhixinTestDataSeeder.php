<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Comment;
use App\Models\InboxNotification;
use App\Models\Order;
use App\Models\Project;
use App\Models\EmailSubscription;
use App\Models\User;
use App\Models\UserPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * OpenClaw 智信：联调/手工测试用初始化数据（依赖 DemoContentSeeder 已写入分类、文章、项目）。
 * 可重复执行：以 email / order_no / code 等唯一键做 upsert。
 */
class ZhixinTestDataSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'password';

    public function run(): void
    {
        $demo = $this->seedUsers();
        $this->assignArticleAuthors($demo['writer']);
        $this->seedThemeAndSkins($demo);
        $this->seedEngagement($demo);
        $this->seedUserPostsAndAudits($demo);
        $this->seedCommerce($demo);
        $this->seedNotifications($demo);
        $this->seedEmailSubscriptions($demo);
        $this->seedSlotsAndAnnouncements($demo);
        $this->seedPoints($demo);
    }

    /**
     * @return array{writer: User, vip: User, svip: User, admin: User}
     */
    private function seedUsers(): array
    {
        $writer = User::query()->updateOrCreate(
            ['email' => 'demo@openclaw.test'],
            [
                'name' => '演示用户',
                'password' => self::DEMO_PASSWORD,
                'role' => 'user',
                'avatar' => null,
                'subscription_ends_at' => null,
                'last_login_at' => now()->subHours(2),
                'last_login_ip' => '127.0.0.1',
            ]
        );

        $vip = User::query()->updateOrCreate(
            ['email' => 'vip@openclaw.test'],
            [
                'name' => 'VIP 会员',
                'password' => self::DEMO_PASSWORD,
                'role' => 'vip',
                'subscription_ends_at' => now()->addMonth(),
                'last_login_at' => now()->subDay(),
                'last_login_ip' => '127.0.0.1',
            ]
        );

        $svip = User::query()->updateOrCreate(
            ['email' => 'svip@openclaw.test'],
            [
                'name' => 'SVIP 会员',
                'password' => self::DEMO_PASSWORD,
                'role' => 'svip',
                'subscription_ends_at' => now()->addMonths(3),
                'last_login_at' => now()->subDays(2),
                'last_login_ip' => '127.0.0.1',
            ]
        );

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@openclaw.test'],
            [
                'name' => '后台管理员',
                'password' => self::DEMO_PASSWORD,
                'role' => 'admin',
                'subscription_ends_at' => null,
                'last_login_at' => now()->subWeek(),
                'last_login_ip' => '127.0.0.1',
            ]
        );

        return compact('writer', 'vip', 'svip', 'admin');
    }

    private function assignArticleAuthors(User $writer): void
    {
        Article::query()->whereNull('author_id')->update(['author_id' => $writer->id]);
    }

    /**
     * @param  array{writer: User, vip: User, svip: User, admin: User}  $demo
     */
    private function seedEmailSubscriptions(array $demo): void
    {
        if (! Schema::hasTable('email_subscriptions')) {
            return;
        }

        EmailSubscription::query()->updateOrCreate(
            ['email' => 'demo@openclaw.test'],
            [
                'user_id' => $demo['writer']->id,
                'subscribed_to' => [EmailSubscription::TOPIC_WEEKLY, EmailSubscription::TOPIC_NOTIFICATION],
                'is_unsubscribed' => false,
                'unsubscribed_at' => null,
            ]
        );

        EmailSubscription::query()->updateOrCreate(
            ['email' => 'vip@openclaw.test'],
            [
                'user_id' => $demo['vip']->id,
                'subscribed_to' => [EmailSubscription::TOPIC_DAILY, EmailSubscription::TOPIC_PROMOTION],
                'is_unsubscribed' => false,
                'unsubscribed_at' => null,
            ]
        );
    }

    /**
     * @param  array{writer: User, vip: User, svip: User, admin: User}  $demo
     */
    private function seedThemeAndSkins(array $demo): void
    {
        DB::table('user_theme_preferences')->updateOrInsert(
            ['user_id' => $demo['writer']->id],
            [
                'theme' => 'default',
                'dark_mode' => false,
                'font_size' => 'medium',
                'follow_system' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('user_theme_preferences')->updateOrInsert(
            ['user_id' => $demo['vip']->id],
            [
                'theme' => 'default',
                'dark_mode' => true,
                'font_size' => 'large',
                'follow_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 与迁移 2026_04_08_100000 中的 style1（免费）、style5（VIP）对应
        $skinFreeId = DB::table('skin_configs')->where('code', 'style1')->value('id');
        $skinVipId = DB::table('skin_configs')->where('code', 'style5')->value('id');

        if ($skinFreeId) {
            DB::table('user_skins')->updateOrInsert(
                ['user_id' => $demo['writer']->id],
                [
                    'skin_id' => $skinFreeId,
                    'activated_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        if ($skinVipId) {
            DB::table('user_skins')->updateOrInsert(
                ['user_id' => $demo['vip']->id],
                [
                    'skin_id' => $skinVipId,
                    'activated_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * @param  array{writer: User, vip: User, svip: User, admin: User}  $demo
     */
    private function seedEngagement(array $demo): void
    {
        $article = Article::query()->where('slug', 'openclaw-ai-weekly-01')->first();
        $project = Project::query()->where('full_name', 'openclaw/ui-kit')->first();

        if ($article) {
            DB::table('favorites')->updateOrInsert(
                [
                    'user_id' => $demo['vip']->id,
                    'favoritable_type' => Article::class,
                    'favoritable_id' => $article->id,
                ],
                ['created_at' => now()]
            );

            DB::table('user_actions')->updateOrInsert(
                [
                    'user_id' => $demo['writer']->id,
                    'actionable_type' => Article::class,
                    'actionable_id' => $article->id,
                    'type' => 'like',
                ],
                ['created_at' => now()]
            );

            DB::table('view_histories')->updateOrInsert(
                [
                    'user_id' => $demo['writer']->id,
                    'viewable_type' => Article::class,
                    'viewable_id' => $article->id,
                ],
                [
                    'viewed_at' => now()->subHours(3),
                ]
            );

            $parent = Comment::query()->firstOrCreate(
                [
                    'user_id' => $demo['vip']->id,
                    'commentable_type' => Article::class,
                    'commentable_id' => $article->id,
                    'parent_id' => null,
                    'content' => '[seed:zhixin-test] 这篇对落地信号总结得很清楚，期待下期。',
                ],
                ['is_hidden' => false, 'like_count' => 2]
            );

            Comment::query()->firstOrCreate(
                [
                    'user_id' => $demo['writer']->id,
                    'commentable_type' => Article::class,
                    'commentable_id' => $article->id,
                    'parent_id' => $parent->id,
                    'content' => '[seed:zhixin-test] 同感，补充一点：评测集要和业务指标对齐。',
                ],
                ['is_hidden' => false, 'like_count' => 0]
            );

            DB::table('comment_likes')->updateOrInsert(
                ['user_id' => $demo['writer']->id, 'comment_id' => $parent->id],
                ['created_at' => now()]
            );

            DB::table('comment_likes')->updateOrInsert(
                ['user_id' => $demo['svip']->id, 'comment_id' => $parent->id],
                ['created_at' => now()]
            );
        }

        if ($project) {
            DB::table('favorites')->updateOrInsert(
                [
                    'user_id' => $demo['writer']->id,
                    'favoritable_type' => Project::class,
                    'favoritable_id' => $project->id,
                ],
                ['created_at' => now()]
            );

            DB::table('user_actions')->updateOrInsert(
                [
                    'user_id' => $demo['svip']->id,
                    'actionable_type' => Project::class,
                    'actionable_id' => $project->id,
                    'type' => 'like',
                ],
                ['created_at' => now()]
            );
        }
    }

    /**
     * @param  array{writer: User, vip: User, svip: User, admin: User}  $demo
     */
    private function seedUserPostsAndAudits(array $demo): void
    {
        $pending = UserPost::query()->updateOrCreate(
            ['user_id' => $demo['writer']->id, 'title' => '[seed:zhixin-test] 独立开发者第一周复盘'],
            [
                'type' => 'experience',
                'content' => "## 本周\n- 上架了一个小工具\n- 流量还在爬坡",
                'category' => '副业项目',
                'tags' => ['复盘', '工具'],
                'visibility' => 'public',
                'status' => 'pending',
                'audit_note' => null,
                'audited_by' => null,
                'audited_at' => null,
            ]
        );

        $approved = UserPost::query()->updateOrCreate(
            ['user_id' => $demo['vip']->id, 'title' => '[seed:zhixin-test] 我用 AI 写技术博客的流水线'],
            [
                'type' => 'tool',
                'content' => "大纲 → 草稿 → 事实核对 → 发布。",
                'category' => 'AI 工具',
                'tags' => ['写作', '自动化'],
                'visibility' => 'public',
                'status' => 'approved',
                'audit_note' => null,
                'audited_by' => $demo['admin']->id,
                'audited_at' => now()->subDay(),
                'view_count' => 120,
                'like_count' => 8,
            ]
        );

        $rejected = UserPost::query()->updateOrCreate(
            ['user_id' => $demo['writer']->id, 'title' => '[seed:zhixin-test] 引流外链合集（测试拒绝）'],
            [
                'type' => 'resource',
                'content' => '大量外链与联系方式，测试审核拒绝场景。',
                'category' => '资源',
                'tags' => ['外链'],
                'visibility' => 'public',
                'status' => 'rejected',
                'audit_note' => '含违规推广信息',
                'audited_by' => $demo['admin']->id,
                'audited_at' => now()->subHours(5),
            ]
        );

        foreach ([$pending, $approved, $rejected] as $post) {
            DB::table('publish_audits')->updateOrInsert(
                ['publish_id' => $post->id],
                [
                    'user_id' => $post->user_id,
                    'auditor_id' => $post->audited_by,
                    'status' => $post->status,
                    'reject_reason' => $post->status === 'rejected' ? $post->audit_note : null,
                    'suggest' => $post->status === 'rejected' ? '请去掉联系方式与外链列表后重提。' : null,
                    'priority' => $post->status === 'pending' ? 10 : 0,
                    'audited_at' => $post->audited_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * @param  array{writer: User, vip: User, svip: User, admin: User}  $demo
     */
    private function seedCommerce(array $demo): void
    {
        DB::table('subscriptions')->updateOrInsert(
            ['user_id' => $demo['vip']->id, 'payment_id' => 'seed-pay-sub-vip-001'],
            [
                'plan' => 'monthly',
                'amount' => 29.00,
                'status' => 'active',
                'started_at' => now()->subWeek(),
                'expires_at' => $demo['vip']->subscription_ends_at,
                'payment_method' => 'wechat',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Order::query()->updateOrCreate(
            ['order_no' => 'ORD-SEED-PAID-001'],
            [
                'user_id' => $demo['vip']->id,
                'product_type' => 'vip',
                'product_id' => 1,
                'amount' => 29.00,
                'status' => 'paid',
                'payment_id' => 'seed-wx-paid-001',
                'payment_method' => 'wechat',
                'paid_at' => now()->subDays(3),
                'remark' => '种子数据：已支付 VIP 月付',
            ]
        );

        Order::query()->updateOrCreate(
            ['order_no' => 'ORD-SEED-PENDING-001'],
            [
                'user_id' => $demo['writer']->id,
                'product_type' => 'vip',
                'product_id' => 1,
                'amount' => 29.00,
                'status' => 'pending',
                'payment_id' => null,
                'payment_method' => null,
                'paid_at' => null,
                'remark' => '种子数据：待支付',
            ]
        );
    }

    /**
     * @param  array{writer: User, vip: User, svip: User, admin: User}  $demo
     */
    private function seedNotifications(array $demo): void
    {
        InboxNotification::query()->updateOrCreate(
            [
                'user_id' => $demo['writer']->id,
                'type' => 'system',
                'title' => '[seed:zhixin-test] 欢迎加入 OpenClaw 智信',
            ],
            [
                'content' => '可在个人中心完善资料并体验换肤与字体设置。',
                'action_url' => '/profile',
                'is_read' => false,
                'read_at' => null,
            ]
        );

        InboxNotification::query()->updateOrCreate(
            [
                'user_id' => $demo['vip']->id,
                'type' => 'subscription',
                'title' => '[seed:zhixin-test] 会员续费提醒',
            ],
            [
                'content' => '您的 VIP 即将到期，可前往价格页续费。',
                'action_url' => '/pricing',
                'is_read' => true,
                'read_at' => now()->subHour(),
            ]
        );
    }

    /**
     * @param  array{writer: User, vip: User, svip: User, admin: User}  $demo
     */
    private function seedSlotsAndAnnouncements(array $demo): void
    {
        $slotHomeBannerId = DB::table('ad_slots')->where('code', 'home-banner')->value('id');
        if (! $slotHomeBannerId) {
            $slotHomeBannerId = DB::table('ad_slots')->insertGetId([
                'name' => '首页横幅',
                'code' => 'home-banner',
                'position' => 'top',
                'type' => 'banner',
                'width' => 1200,
                'height' => 280,
                'is_active' => true,
                'sort' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $floatSlotId = DB::table('ad_slots')->where('code', 'float-corner')->value('id');
        if (! $floatSlotId) {
            DB::table('ad_slots')->insert([
                'name' => '右下角浮动',
                'code' => 'float-corner',
                'position' => 'right',
                'type' => 'float',
                'width' => 120,
                'height' => 160,
                'is_active' => true,
                'sort' => 2,
                'default_title' => null,
                'default_image_url' => null,
                'default_link_url' => null,
                'default_content' => null,
                'show_default_when_empty' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $detailSlots = [
            ['name' => '文章详情顶部', 'code' => 'article-top', 'position' => 'top', 'type' => 'inline', 'width' => 728, 'height' => 90],
            ['name' => '文章侧栏', 'code' => 'article-sidebar', 'position' => 'right', 'type' => 'sidebar', 'width' => 300, 'height' => 250],
            ['name' => '项目详情顶部', 'code' => 'project-top', 'position' => 'top', 'type' => 'inline', 'width' => 728, 'height' => 90],
            ['name' => '项目侧栏', 'code' => 'project-sidebar', 'position' => 'right', 'type' => 'sidebar', 'width' => 300, 'height' => 250],
        ];
        foreach ($detailSlots as $ds) {
            if (DB::table('ad_slots')->where('code', $ds['code'])->exists()) {
                continue;
            }
            DB::table('ad_slots')->insert(array_merge($ds, [
                'is_active' => true,
                'sort' => 5,
                'default_title' => '开通 VIP 浏览全文',
                'default_image_url' => null,
                'default_link_url' => '/max/pricing',
                'default_content' => null,
                'show_default_when_empty' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        DB::table('announcements')->updateOrInsert(
            ['title' => '[seed:zhixin-test] 系统维护预告'],
            [
                'content' => '<p>本周日凌晨 2:00-4:00 进行数据库维护，期间可能短暂不可用。</p>',
                'priority' => 'high',
                'display_position' => 'top',
                'is_floating' => false,
                'cover_image' => null,
                'is_published' => true,
                'published_at' => now()->subDay(),
                'expires_at' => now()->addMonth(),
                'created_by' => $demo['admin']->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('side_hustle_cases')->updateOrInsert(
            ['slug' => 'seed-zhixin-demo-case'],
            [
                'title' => '[seed:zhixin-test] 知识付费副业入门',
                'summary' => '用专栏 + 社群完成冷启动的示例路径，数据为演示用途。',
                'content' => "## 适合谁\n上班族，每天可投入 1～2 小时。\n\n## 步骤概览\n1. 确定细分选题\n2. 公域引流到私域\n3. 低价体验课转化年课",
                'category' => 'online',
                'type' => 'content',
                'startup_cost' => '500',
                'time_investment' => '每天 1～2 小时',
                'estimated_income' => 3000,
                'actual_income' => null,
                'income_screenshots' => null,
                'steps' => null,
                'tools' => json_encode(['Notion', '飞书']),
                'pitfalls' => null,
                'willing_to_consult' => false,
                'contact_info' => null,
                'visibility' => 'public',
                'status' => 'approved',
                'audit_note' => null,
                'audited_by' => $demo['admin']->id,
                'audited_at' => now(),
                'view_count' => 0,
                'like_count' => 0,
                'comment_count' => 0,
                'favorite_count' => 0,
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('ai_tool_monetization')->updateOrInsert(
            ['slug' => 'seed-zhixin-demo-tool'],
            [
                'tool_name' => '[seed:zhixin-test] 示例写作助手',
                'tool_url' => 'https://example.com/tool',
                'category' => 'text',
                'available_in_china' => true,
                'pricing_model' => 'subscription',
                'content' => '<p>可用于商详文案、小红书笔记批量起稿。<strong>以下为演示 HTML</strong>，正式内容请在后台维护。</p><ul><li>场景：电商详情页</li><li>报价参考：￥50～200 / 篇</li></ul>',
                'monetization_scenes' => null,
                'prompt_templates' => null,
                'pricing_reference' => null,
                'channels' => null,
                'delivery_standards' => null,
                'visibility' => 'public',
                'view_count' => 0,
                'like_count' => 0,
                'favorite_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * @param  array{writer: User, vip: User, svip: User, admin: User}  $demo
     */
    private function seedPoints(array $demo): void
    {
        $has = DB::table('points')
            ->where('user_id', $demo['writer']->id)
            ->where('description', '[seed:zhixin-test] 每日登录')
            ->exists();

        if ($has) {
            return;
        }

        $commentId = Comment::query()
            ->where('content', 'like', '[seed:zhixin-test] 这篇%')
            ->value('id');

        DB::table('points')->updateOrInsert(
            [
                'user_id' => $demo['writer']->id,
                'description' => '[seed:zhixin-test] 每日登录',
            ],
            [
                'amount' => 10,
                'balance' => 10,
                'type' => 'earn',
                'category' => 'daily',
                'reference_type' => null,
                'reference_id' => null,
                'created_at' => now()->subDay(),
            ]
        );
        DB::table('points')->updateOrInsert(
            [
                'user_id' => $demo['writer']->id,
                'description' => '[seed:zhixin-test] 发表评论',
            ],
            [
                'amount' => 5,
                'balance' => 15,
                'type' => 'earn',
                'category' => 'content',
                'reference_type' => $commentId ? Comment::class : null,
                'reference_id' => $commentId,
                'created_at' => now(),
            ]
        );
    }
}
