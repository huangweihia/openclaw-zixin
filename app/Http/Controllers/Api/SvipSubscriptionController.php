<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SvipSubscription;
use App\Models\Article;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SvipSubscriptionController extends Controller
{
    /**
     * 获取所有活跃的 SVIP 订阅配置
     * 
     * GET /api/svip/subscriptions/list
     * 
     * Query Parameters:
     * - due=true: Only return subscriptions that are due (last_fetch_at is null or > 30 minutes ago)
     */
    public function list(Request $request)
    {
        // 验证 API Token
        $token = $request->header('X-API-Token');
        $expectedToken = env('SVIP_SUBSCRIPTION_TOKEN', 'svip-subscription-2026');
        
        if ($token !== $expectedToken) {
            Log::warning("⚠️ SVIP 订阅 API Token 认证失败：" . ($token ?? 'empty'));
            return response()->json([
                'success' => false,
                'message' => '认证失败',
                'subscriptions' => []
            ], 401);
        }
        
        Log::info("🔓 SVIP 订阅列表请求 - Token 验证通过");
        
        // 检查是否需要过滤 due 订阅
        $dueOnly = $request->query('due', false) === 'true';
        
        $query = SvipSubscription::where('is_active', true);
        
        if ($dueOnly) {
            Log::info("📋 请求 due 订阅列表（last_fetch_at 为 null 或超过 30 分钟）");
            // 只返回 last_fetch_at 为 null 或超过 30 分钟的订阅
            $query->where(function ($q) {
                $q->whereNull('last_fetch_at')
                  ->orWhere('last_fetch_at', '<', now()->subMinutes(30));
            });
        }
        
        // 获取所有活跃的订阅
        $subscriptions = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($subscription) use ($dueOnly) {
                $lastFetchAt = $subscription->last_fetch_at;
                $isDue = false;
                
                if ($lastFetchAt === null) {
                    $isDue = true;
                } else {
                    $isDue = $lastFetchAt->lt(now()->subMinutes(30));
                }
                
                return [
                    'id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                    'name' => $subscription->name,
                    'keywords' => $subscription->keywords ?? [],
                    'exclude_keywords' => $subscription->exclude_keywords ?? [],
                    'sources' => $subscription->sources ?? [],
                    'frequency' => $subscription->frequency,
                    'push_methods' => $subscription->push_methods ?? [],
                    'last_fetch_at' => $lastFetchAt?->toIso8601String(),
                    'last_run_at' => $lastFetchAt?->toIso8601String(),  // 兼容旧版字段名
                    'due' => $isDue,
                    'created_at' => $subscription->created_at?->toIso8601String(),
                ];
            });
        
        Log::info("✅ SVIP 订阅列表返回成功，数量：" . count($subscriptions) . ($dueOnly ? " (due only)" : ""));
        
        return response()->json([
            'success' => true,
            'subscriptions' => $subscriptions,
            'count' => count($subscriptions),
            'due_only' => $dueOnly
        ]);
    }
    
    /**
     * 接收 SVIP 订阅数据推送
     * 
     * POST /api/svip/subscriptions/data
     */
    public function pushData(Request $request)
    {
        // 验证 API Token
        $token = $request->header('X-API-Token');
        $expectedToken = env('SVIP_SUBSCRIPTION_TOKEN', 'svip-subscription-2026');
        
        if ($token !== $expectedToken) {
            Log::error("❌ SVIP 数据推送 Token 认证失败");
            return response()->json(['success' => false, 'message' => '认证失败'], 401);
        }
        
        $data = $request->json()->all();
        $subscriptionId = $data['subscription_id'] ?? null;
        $items = $data['items'] ?? [];
        
        Log::info("📥 SVIP 数据推送请求 - 订阅 ID: {$subscriptionId}, 内容数量：" . count($items));
        
        if (!$subscriptionId) {
            return response()->json(['success' => false, 'message' => '缺少 subscription_id'], 400);
        }
        
        // 验证订阅是否存在
        $subscription = SvipSubscription::find($subscriptionId);
        if (!$subscription) {
            return response()->json(['success' => false, 'message' => '订阅不存在'], 404);
        }
        
        $saved = 0;
        $failed = 0;
        $pushed = 0;
        
        foreach ($items as $index => $item) {
            try {
                $itemType = $item['type'] ?? 'article';
                
                if ($itemType === 'article') {
                    $result = $this->saveArticle($subscriptionId, $item);
                } elseif ($itemType === 'project') {
                    $result = $this->saveProject($subscriptionId, $item);
                } else {
                    Log::warning("⏭️ [{$index}] 未知类型：{$itemType}");
                    continue;
                }
                
                if ($result['saved']) {
                    $saved++;
                } else {
                    $failed++;
                }
                
                if ($result['pushed']) {
                    $pushed++;
                }
            } catch (\Exception $e) {
                Log::error("❌ [{$index}] 保存失败：" . $e->getMessage());
                $failed++;
            }
        }
        
        // 更新订阅的最后获取时间
        $subscription->update([
            'last_fetch_at' => now(),
            'last_fetch_count' => $saved,
        ]);
        
        // 触发推送（根据订阅配置的推送方式）
        if ($pushed > 0) {
            $this->triggerPush($subscription, $pushed);
        }
        
        Log::info("✅ SVIP 数据推送完成 - 保存：{$saved}, 失败：{$failed}, 推送：{$pushed}");
        
        return response()->json([
            'success' => true,
            'message' => "成功保存 {$saved} 条内容",
            'saved' => $saved,
            'failed' => $failed,
            'pushed' => $pushed
        ]);
    }
    
    /**
     * 保存文章
     */
    protected function saveArticle(int $subscriptionId, array $item): array
    {
        $url = $item['url'] ?? null;
        
        // 检查是否已存在
        if ($url && Article::where('source_url', $url)->exists()) {
            Log::info("⏭️ 文章已存在，跳过：" . $url);
            return ['saved' => false, 'pushed' => false];
        }
        
        $article = Article::create([
            'title' => $item['title'] ?? '无标题',
            'slug' => \Illuminate\Support\Str::slug($item['title']) . '-' . time() . '-' . rand(1000, 9999),
            'summary' => $item['description'] ?? '',
            'content' => $item['content'] ?? '',
            'source_url' => $url,
            'cover_image' => $item['cover_image'] ?? null,
            'is_published' => true,
            'is_vip_only' => true,
            'svip_subscription_id' => $subscriptionId,
            'published_at' => now(),
        ]);
        
        Log::info("✅ 文章保存成功，ID: " . $article->id);
        
        return ['saved' => true, 'pushed' => true];
    }
    
    /**
     * 保存项目
     */
    protected function saveProject(int $subscriptionId, array $item): array
    {
        $url = $item['url'] ?? null;
        
        // 检查是否已存在
        if ($url && Project::where('url', $url)->exists()) {
            Log::info("⏭️ 项目已存在，跳过：" . $url);
            return ['saved' => false, 'pushed' => false];
        }
        
        $project = Project::create([
            'name' => $item['name'] ?? '未知项目',
            'full_name' => $item['name'] ?? '未知项目',
            'description' => $item['description'] ?? '暂无描述',
            'url' => $url,
            'stars' => (int) ($item['stars'] ?? 0),
            'language' => $item['language'] ?? null,
            'is_featured' => ($item['stars'] ?? 0) > 1000,
            'svip_subscription_id' => $subscriptionId,
            'collected_at' => now(),
        ]);
        
        Log::info("✅ 项目保存成功，ID: " . $project->id);
        
        return ['saved' => true, 'pushed' => true];
    }
    
    /**
     * 触发推送
     */
    protected function triggerPush(SvipSubscription $subscription, int $count): void
    {
        $pushMethods = $subscription->push_methods ?? [];
        
        foreach ($pushMethods as $method) {
            switch ($method) {
                case 'email':
                    // TODO: 发送邮件通知
                    Log::info("📧 准备发送邮件通知给用户 {$subscription->user_id}");
                    break;
                case 'wechat':
                    // TODO: 发送微信通知
                    Log::info("💬 准备发送微信通知给用户 {$subscription->user_id}");
                    break;
            }
        }
    }
}
