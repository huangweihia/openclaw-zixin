<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $aiNews = Category::query()->updateOrCreate(
            ['slug' => 'ai-news'],
            ['name' => 'AI 资讯', 'description' => '人工智能行业与产品动态', 'sort' => 40, 'is_premium' => false]
        );
        $techTutorial = Category::query()->updateOrCreate(
            ['slug' => 'tech-tutorial'],
            ['name' => '技术教程', 'description' => '实战教程与最佳实践', 'sort' => 30, 'is_premium' => false]
        );
        $aiTools = Category::query()->updateOrCreate(
            ['slug' => 'ai-tools'],
            ['name' => 'AI 工具', 'description' => 'AI 工具与开源项目', 'sort' => 20, 'is_premium' => false]
        );
        $sideHustle = Category::query()->updateOrCreate(
            ['slug' => 'side-hustle'],
            ['name' => '副业项目', 'description' => '可落地的副业与变现案例', 'sort' => 10, 'is_premium' => false]
        );

        $articles = [
            [
                'slug' => 'openclaw-ai-weekly-01',
                'title' => 'OpenClaw 智信周刊：大模型应用落地的五个信号',
                'summary' => '从企业采购、开发者工具链到合规要求，梳理本周值得关注的趋势。',
                'content' => '<p>企业侧对「可审计、可私有化」的推理服务需求明显上升；同时，轻量 Agent 编排框架在中小团队快速普及。</p><p>建议读者关注：数据血缘、提示词版本管理、以及评测集与业务指标的对齐方式。</p>',
                'category_id' => $aiNews->id,
                'is_vip' => false,
                'view_count' => 1280,
                'like_count' => 86,
            ],
            [
                'slug' => 'laravel-skin-css-variables',
                'title' => '用 CSS 变量做全局换肤：Laravel Blade 中的工程化实践',
                'summary' => '一套 skins.css + data-skin 即可覆盖全站，避免后期逐页改色。',
                'content' => '<p>将品牌色、背景层级、渐变定义为变量，组件只引用变量名，不硬编码十六进制颜色。</p><p>切换时写入 <code>data-skin</code> 与 <code>localStorage</code>，首屏前用内联脚本恢复，可避免闪烁。</p>',
                'category_id' => $techTutorial->id,
                'is_vip' => false,
                'view_count' => 960,
                'like_count' => 64,
            ],
            [
                'slug' => 'vip-exclusive-prompt-library',
                'title' => 'VIP 专属：高转化营销提示词模板库（节选）',
                'summary' => '面向 B2B SaaS 的邮件序列与落地页改写提示词，完整版仅会员可见。',
                'content' => '<p>以下为完整模板库正文……（会员可见）</p><p>包含：冷邮件三轮跟进、案例研究转长文、以及多语言本地化检查清单。</p>',
                'category_id' => $aiNews->id,
                'is_vip' => true,
                'view_count' => 420,
                'like_count' => 31,
            ],
            [
                'slug' => 'side-income-from-api-wrappers',
                'title' => '副业思路：把常用 API 封装成「小工具站」的注意事项',
                'summary' => '从定价、限流到内容合规，避免踩坑。',
                'content' => '<p>优先选择有清晰 ToS 的 API；前端展示与密钥保管要分离，计费与配额建议用队列异步统计。</p>',
                'category_id' => $sideHustle->id,
                'is_vip' => false,
                'view_count' => 2100,
                'like_count' => 142,
            ],
            [
                'slug' => 'embedding-cache-strategy',
                'title' => '向量检索成本优化：Embedding 缓存与分段策略',
                'summary' => '在 RAG 场景下如何把重复计算压到最低。',
                'content' => '<p>对稳定文档使用内容哈希作为缓存键；对频繁变更段落采用「段落级」向量化与失效策略。</p>',
                'category_id' => $techTutorial->id,
                'is_vip' => false,
                'view_count' => 540,
                'like_count' => 40,
            ],
        ];

        foreach ($articles as $row) {
            Article::query()->updateOrCreate(
                ['slug' => $row['slug']],
                array_merge($row, [
                    'is_published' => true,
                    'published_at' => now()->subDays(random_int(1, 30)),
                ])
            );
        }

        $projects = [
            [
                'name' => 'OpenClaw UI Kit',
                'full_name' => 'openclaw/ui-kit',
                'description' => '与换肤变量对齐的 Blade 组件与样式骨架，便于快速拼装页面。',
                'url' => 'https://github.com/openclaw/ui-kit',
                'language' => 'PHP',
                'stars' => 1820,
                'forks' => 96,
                'score' => 4.6,
                'tags' => ['laravel', 'blade', 'css'],
                'monetization' => '可基于组件库提供付费主题包或企业内训。',
                'difficulty' => 'medium',
                'is_featured' => true,
                'is_vip' => false,
                'category_id' => $aiTools->id,
            ],
            [
                'name' => 'Agent Flow Lab',
                'full_name' => 'openclaw/agent-flow-lab',
                'description' => '可视化编排多步 Agent，支持人类在环与工具调用审计。',
                'url' => 'https://github.com/openclaw/agent-flow-lab',
                'language' => 'TypeScript',
                'stars' => 960,
                'forks' => 120,
                'score' => 4.4,
                'tags' => ['agent', 'workflow', 'react'],
                'monetization' => 'SaaS 按席位订阅；高级模板市场分成。',
                'difficulty' => 'hard',
                'is_featured' => true,
                'is_vip' => true,
                'category_id' => $aiTools->id,
            ],
            [
                'name' => 'Side Hustle Radar',
                'full_name' => 'openclaw/side-hustle-radar',
                'description' => '聚合公开数据集与社区信号，筛选可复制的副业方向。',
                'url' => 'https://github.com/openclaw/side-hustle-radar',
                'language' => 'Python',
                'stars' => 640,
                'forks' => 58,
                'score' => 4.1,
                'tags' => ['python', 'data', 'automation'],
                'monetization' => '付费周报、定向推送与联盟佣金页面。',
                'difficulty' => 'easy',
                'is_featured' => false,
                'is_vip' => false,
                'category_id' => $sideHustle->id,
            ],
        ];

        foreach ($projects as $row) {
            Project::query()->updateOrCreate(
                ['url' => $row['url']],
                $row
            );
        }
    }
}
