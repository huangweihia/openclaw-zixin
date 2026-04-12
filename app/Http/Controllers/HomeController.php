<?php

namespace App\Http\Controllers;

use App\Models\AiToolMonetization;
use App\Models\Article;
use App\Models\PersonalityQuizSetting;
use App\Models\Project;
use App\Support\PricingConfig;
use App\Support\VipActivityFeed;
use App\Models\PersonalityQuestion;
use App\Models\PrivateTrafficSop;
use App\Models\SideHustleCase;
use App\Models\SiteTestimonial;
use App\Models\UserPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $this->seedHomeDemoContentIfEmpty();
        VipActivityFeed::seedDemoIfNeeded(50);
        $homeStats = $this->homeStats();

        $user = $request->user();
        $canVip = (bool) $user?->canAccessVipExclusiveContent();
        $canSvip = (bool) $user?->canAccessSvipExclusiveContent();
        $unlockedPreview = (bool) $user?->canAccessVipExclusiveContent();
        $vipPreviews = $this->buildVipPreviews($unlockedPreview);

        $pricingPlans = $this->homePricingPlans();
        $testimonials = $this->homeTestimonials();
        $featuredArticles = $this->featuredArticles();
        $featuredProjects = $this->featuredProjects();
        $featuredCases = $this->featuredCases();
        $featuredUserPosts = $this->featuredUserPosts();

        $personalityQuizEnabled = true;
        if (Schema::hasTable('personality_quiz_settings')) {
            $personalityQuizEnabled = (int) (PersonalityQuizSetting::getValue('enabled', '1') ?? 1) === 1;
        }

        $personalityQuizAvailable = $personalityQuizEnabled
            && Schema::hasTable('personality_questions')
            && PersonalityQuestion::query()->exists();

        $vipActivities = VipActivityFeed::recent(20);

        return view('home', compact(
            'homeStats',
            'vipPreviews',
            'pricingPlans',
            'testimonials',
            'featuredArticles',
            'featuredProjects',
            'featuredCases',
            'featuredUserPosts',
            'canVip',
            'canSvip',
            'personalityQuizAvailable',
            'vipActivities'
        ));
    }

    /**
     * 首页数据展示区（真实读库，但展示时按运营要求做倍率/固定文案）。
     *
     * @return array{users:int,cases:int,tools:int,revenue_text:string}
     */
    private function homeStats(): array
    {
        $users = Schema::hasTable('users') ? (int) DB::table('users')->count() : 0;
        $cases = Schema::hasTable('side_hustle_cases') ? (int) DB::table('side_hustle_cases')->count() : 0;
        $tools = Schema::hasTable('ai_tool_monetizations') ? (int) DB::table('ai_tool_monetizations')->count() : 0;

        return [
            // 展示时乘 10（避免首页数据过小）
            'users' => $users * 10,
            'cases' => $cases * 10,
            'tools' => $tools * 10,
            // 运营要求：累计变现固定文案
            'revenue_text' => '200 万+',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildVipPreviews(bool $unlocked): array
    {
        $case = SideHustleCase::query()
            ->where('status', 'approved')
            ->where(function ($q) use ($unlocked) {
                $q->where('visibility', 'public');
                if ($unlocked) {
                    $q->orWhere('visibility', 'vip');
                }
            })
            ->orderByDesc('audited_at')
            ->orderByDesc('id')
            ->first();

        $tool = AiToolMonetization::query()
            ->where(function ($q) use ($unlocked) {
                $q->where('visibility', 'public');
                if ($unlocked) {
                    $q->orWhere('visibility', 'vip');
                }
            })
            ->orderByDesc('id')
            ->first();

        $sop = PrivateTrafficSop::query()
            ->where(function ($q) use ($unlocked) {
                $q->where('visibility', 'public');
                if ($unlocked) {
                    $q->orWhere('visibility', 'vip');
                }
            })
            ->orderByDesc('id')
            ->first();

        $toolSummary = '《Midjourney》· 5 大变现场景';
        if ($tool) {
            $scenes = $tool->monetization_scenes;
            if (is_array($scenes) && $scenes !== []) {
                $toolSummary = '场景：'.implode('、', array_slice(array_map('strval', $scenes), 0, 3));
            } else {
                $stripped = Str::limit(trim(strip_tags((string) $tool->content)), 100);
                if ($stripped !== '') {
                    $toolSummary = $stripped;
                }
            }
        }

        return [
            $this->previewCard('💼', '副业案例', $case?->title ?? '副业案例', $case?->summary ?? '《小红书爆款笔记》· 启动成本：0 元', $unlocked, $case ? route('cases.show', $case) : route('cases.index')),
            $this->previewCard('🛠️', 'AI 工具', $tool?->tool_name ?? 'AI 工具', $toolSummary, $unlocked, $tool ? route('tools.show', $tool) : route('tools.index')),
            $this->previewCard('📈', '运营 SOP', $sop?->title ?? '运营 SOP', $sop?->summary ?? '《小红书起号》· 30 天万粉', $unlocked, $sop ? route('sops.show', $sop) : route('sops.index')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function previewCard(string $icon, string $fallbackTitle, string $title, string $summary, bool $unlocked, string $url): array
    {
        return [
            'icon' => $icon,
            'title' => $title ?: $fallbackTitle,
            'summary' => $summary ?: '精彩内容等你发现',
            'locked' => ! $unlocked,
            'url' => $url,
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function homePricingPlans()
    {
        $catalog = PricingConfig::catalogMerged();

        return collect([
            [
                'name' => $catalog['free']['name'],
                'price' => 0,
                'period' => '永久',
                'features' => $catalog['free']['features'],
                'highlight' => false,
                'plan_key' => 'free',
                'deadline_at' => null,
            ],
            [
                'name' => $catalog['vip']['name'],
                'price' => 29,
                'period' => '月',
                'features' => $catalog['vip']['features'],
                'highlight' => true,
                'plan_key' => 'vip',
                'original_price' => $catalog['vip']['original_price_yuan'] ?? null,
                'promo_label' => $catalog['vip']['promo_label'] ?? '',
                'spots_label' => $catalog['vip']['spots_label'] ?? '',
                'deadline_at' => $catalog['vip']['deadline_at'] ?? null,
            ],
            [
                'name' => $catalog['svip']['name'],
                'price' => 99,
                'period' => '月',
                'features' => $catalog['svip']['features'],
                'highlight' => false,
                'plan_key' => 'svip',
                'original_price' => $catalog['svip']['original_price_yuan'] ?? null,
                'promo_label' => $catalog['svip']['promo_label'] ?? '',
                'spots_label' => $catalog['svip']['spots_label'] ?? '',
                'deadline_at' => $catalog['svip']['deadline_at'] ?? null,
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, SiteTestimonial>
     */
    private function homeTestimonials()
    {
        if (! Schema::hasTable('site_testimonials')) {
            return collect();
        }

        $this->seedDemoTestimonialsIfEmpty();

        return SiteTestimonial::query()->published()->ordered()->limit(6)->get();
    }

    private function seedDemoTestimonialsIfEmpty(): void
    {
        if (SiteTestimonial::query()->exists()) {
            return;
        }

        $now = now();
        $rows = [
            [
                'display_name' => '张先生',
                'caption' => 'VIP 会员 · 3 个月',
                'body' => '通过平台的咨询洞察与案例库，我把零散信息整理成可交付方案，副业收入更稳定了。',
                'rating' => 5,
                'avatar_initial' => '张',
                'gradient_from' => 'from-blue-400',
                'gradient_to' => 'to-blue-600',
                'sort_order' => 30,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'display_name' => '李女士',
                'caption' => 'SVIP · 定制数据订阅',
                'body' => '按订阅节奏收到 OpenClaw 侧整理的情报摘要，省掉自己盯盘的时间，决策快很多。',
                'rating' => 5,
                'avatar_initial' => '李',
                'gradient_from' => 'from-green-400',
                'gradient_to' => 'to-green-600',
                'sort_order' => 20,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'display_name' => '王先生',
                'caption' => 'VIP 会员 · 1 年',
                'body' => '私域 SOP 与工具地图很实在，照着清单做一周就能看到转化结构清晰起来。',
                'rating' => 5,
                'avatar_initial' => '王',
                'gradient_from' => 'from-purple-400',
                'gradient_to' => 'to-purple-600',
                'sort_order' => 10,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        SiteTestimonial::query()->insert($rows);
    }

    private function seedHomeDemoContentIfEmpty(): void
    {
        if (! Schema::hasTable('side_hustle_cases')) {
            return;
        }

        $hasPublicCase = SideHustleCase::query()
            ->where('status', 'approved')
            ->where('visibility', 'public')
            ->exists();

        if (! $hasPublicCase) {
            SideHustleCase::query()->firstOrCreate(
                ['slug' => 'oc-demo-xhs-notes'],
                [
                    'title' => '小红书爆款笔记：0 成本起号演示数据',
                    'summary' => '演示用案例，用于首页与空库兜底；可替换为真实内容。',
                    'content' => "## 概述\n\n此为系统生成的**演示案例**，便于验收首页「真实数据」展示。\n\n## 步骤\n\n1. 定位赛道\n2. 批量产出封面\n3. 复盘数据\n",
                    'category' => 'online',
                    'type' => 'content',
                    'startup_cost' => '0',
                    'time_investment' => '每周约 8 小时',
                    'visibility' => 'public',
                    'status' => 'approved',
                    'audited_at' => now(),
                ]
            );
        }

        if (Schema::hasTable('ai_tool_monetization')) {
            $hasTool = AiToolMonetization::query()->where('visibility', 'public')->exists();
            if (! $hasTool) {
                AiToolMonetization::query()->firstOrCreate(
                    ['slug' => 'oc-demo-midjourney'],
                    [
                        'tool_name' => 'Midjourney（演示）',
                        'tool_url' => 'https://www.midjourney.com',
                        'category' => 'image',
                        'pricing_model' => 'subscription',
                        'content' => '<p>演示用 <strong>AI 工具</strong> 条目：变现场景包括头像定制、电商主图、品牌视觉等。</p>',
                        'visibility' => 'public',
                    ]
                );
            }
        }

        if (Schema::hasTable('private_traffic_sops')) {
            $hasSop = PrivateTrafficSop::query()->where('visibility', 'public')->exists();
            if (! $hasSop) {
                PrivateTrafficSop::query()->firstOrCreate(
                    ['slug' => 'oc-demo-sop-xhs'],
                    [
                        'title' => '小红书起号 SOP（演示）',
                        'summary' => '30 天万粉路径演示数据，可后台替换。',
                        'content' => "## 目标\n\n演示用 SOP，用于首页与列表验收。\n\n## 清单\n\n- 账号定位\n- 内容日历\n- 复盘表\n",
                        'platform' => 'xiaohongshu',
                        'type' => 'operation',
                        'visibility' => 'public',
                    ]
                );
            }
        }
    }

    private function featuredArticles()
    {
        return Article::query()
            ->where('is_published', true)
            ->orderByDesc('like_count')
            ->orderByDesc('view_count')
            ->limit(8)
            ->get(['id', 'title', 'slug', 'like_count', 'view_count', 'is_vip', 'is_vip_only']);
    }

    private function featuredProjects()
    {
        return Project::query()
            ->orderByDesc('stars')
            ->orderByDesc('forks')
            ->limit(8)
            ->get(['id', 'name', 'stars', 'forks', 'is_vip']);
    }

    private function featuredCases()
    {
        return SideHustleCase::query()
            ->where('status', 'approved')
            ->whereIn('visibility', ['public', 'vip'])
            ->orderByDesc('like_count')
            ->orderByDesc('view_count')
            ->limit(8)
            ->get(['id', 'title', 'slug', 'like_count', 'view_count', 'visibility']);
    }

    /**
     * 首页「精品投稿」：加热权重优先，其次热度。
     */
    private function featuredUserPosts()
    {
        if (! Schema::hasTable('user_posts') || ! Schema::hasColumn('user_posts', 'boost_weight')) {
            return collect();
        }

        return UserPost::query()
            ->publicFeed()
            ->orderByDesc('boost_weight')
            ->orderByDesc('heat_score')
            ->orderByDesc('like_count')
            ->limit(8)
            ->get(['id', 'title', 'type', 'visibility', 'like_count', 'view_count', 'boost_weight', 'heat_score']);
    }
}
