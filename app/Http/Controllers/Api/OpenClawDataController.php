<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\OpenclawTaskLog;
use App\Models\Project;
use App\Models\SideHustleCase;
use App\Models\AiToolMonetization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * OpenClaw 数据接收控制器
 * 
 * 接收 OpenClaw 定时任务采集的数据
 * 支持 4 种类型：articles, projects, side_hustle_cases, ai_tool_monetization
 * 
 * 特性：
 * - 自动去重（基于 URL）
 * - 内容质量验证（字数检查）
 * - 事务处理（保证数据一致性）
 * - 详细日志记录
 */
class OpenClawDataController extends Controller
{
    /**
     * 接收 OpenClaw 推送的数据
     * 
     * POST /api/openclaw/data
     * Headers: Content-Type: application/json, X-API-Token: openclaw-ai-fetcher-2026
     * 
     * Body: {
     *   "type": "articles|projects|side_hustle_cases|ai_tool_monetization",
     *   "items": [...]
     * }
     */
    public function store(Request $request)
    {
        $startedAt = now();
        $itemsCount = count($request->input('items', []));
        $typeFromRequest = (string) $request->input('type', '');

        // 只要请求打到接口，就先落一条任务日志（便于后台可观测）
        $taskLog = OpenclawTaskLog::query()->create([
            'task_name' => 'openclaw:data:push',
            'task_id' => null,
            'task_type' => OpenclawTaskLog::TYPE_AI_CONTENT,
            'status' => OpenclawTaskLog::STATUS_SUCCESS, // 先写入，后续会更新为最终状态
            'duration_ms' => null,
            'data_summary' => [
                'type' => $typeFromRequest,
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ],
            'total_items' => $itemsCount,
            'success_count' => 0,
            'failed_count' => 0,
            'skipped_count' => 0,
            'api_endpoint' => '/api/openclaw/data',
            // 这里是“对方推送到我方”的入站请求，用 push_status 表示接收结果
            'push_status' => OpenclawTaskLog::PUSH_SUCCESS,
            'push_response' => 'received',
            'error_message' => null,
            'error_details' => null,
            'started_at' => $startedAt,
            'finished_at' => null,
        ]);

        // 🔥🔥 测试输出 - 证明控制器被调用 🔥🔥🔥
        $testOutput = "\n\n===== 🦀 OPENCLAW CONTROLLER CALLED! 🦀 =====\n";
        $testOutput .= "Time: " . now()->toDateTimeString() . "\n";
        $testOutput .= "IP: " . $request->ip() . "\n";
        $testOutput .= "Type: " . $request->input('type') . "\n";
        $testOutput .= "Items: " . count($request->input('items', [])) . "\n";
        $testOutput .= "Token: " . $request->header('X-API-Token') . "\n";
        $testOutput .= "===== 🦀 END TEST OUTPUT 🦀 =====\n\n";
        
        // 输出到日志
        \Log::info($testOutput);
        
        // 同时输出到 error_log（确保能看到）
        error_log($testOutput);
        
        // 记录请求日志
        \Log::info('===== OpenClaw 数据推送开始 =====', [
            'time' => now()->toDateTimeString(),
            'ip' => $request->ip(),
            'type' => $request->input('type'),
            'items_count' => count($request->input('items', [])),
            'token' => $request->header('X-API-Token'),
            'user_agent' => $request->userAgent(),
        ]);

        // 验证 Token
        $token = $request->header('X-API-Token');
        if ($token !== config('services.openclaw.token')) {
            \Log::error('OpenClaw Token 验证失败', [
                'provided_token' => $token,
                'expected_token' => config('services.openclaw.token')
            ]);

            $durationMs = (int) max(0, now()->diffInMilliseconds($startedAt));
            $taskLog->update([
                'status' => OpenclawTaskLog::STATUS_ERROR,
                'duration_ms' => $durationMs,
                'push_status' => OpenclawTaskLog::PUSH_FAILED,
                'push_response' => 'unauthorized',
                'error_message' => 'Unauthorized: Invalid API Token',
                'finished_at' => now(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid API Token'
            ], 401);
        }

        \Log::info('Token 验证通过');

        // 验证请求数据（如果 422，也要把原因写进任务日志）
        try {
            $validated = $request->validate([
                'type' => 'required|string|in:articles,projects,side_hustle_cases,ai_tool_monetization',
                'items' => 'required|array|min:1'
            ], [
                'type.in' => '类型必须是：articles, projects, side_hustle_cases, ai_tool_monetization'
            ]);
        } catch (ValidationException $e) {
            $durationMs = (int) max(0, now()->diffInMilliseconds($startedAt));
            $taskLog->update([
                'status' => OpenclawTaskLog::STATUS_ERROR,
                'duration_ms' => $durationMs,
                'push_status' => OpenclawTaskLog::PUSH_FAILED,
                'push_response' => 'validation_failed',
                'error_message' => 'Validation failed',
                'error_details' => json_encode($e->errors(), JSON_UNESCAPED_UNICODE),
                'finished_at' => now(),
            ]);
            throw $e;
        }

        $type = $validated['type'];
        $items = $validated['items'];

        $saved = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];

        \Log::info("开始处理 {$type} 类型数据，共 " . count($items) . " 条");

        DB::beginTransaction();

        try {
            foreach ($items as $index => $item) {
                try {
                    // 内容质量验证
                    $qualityCheck = $this->validateContentQuality($type, $item);
                    if (!$qualityCheck['passed']) {
                        $failed++;
                        $errors[] = [
                            'index' => $index,
                            'reason' => $qualityCheck['message']
                        ];
                        \Log::warning("内容验证失败", [
                            'type' => $type,
                            'index' => $index,
                            'reason' => $qualityCheck['message']
                        ]);
                        continue;
                    }

                    $result = match ($type) {
                        'articles' => $this->saveArticle($item),
                        'projects' => $this->saveProject($item),
                        'side_hustle_cases' => $this->saveSideHustleCase($item),
                        'ai_tool_monetization' => $this->saveAiToolMonetization($item),
                        default => 'failed'
                    };

                    if ($result === 'saved') {
                        $saved++;
                        \Log::info("保存成功", ['type' => $type, 'index' => $index]);
                    } elseif ($result === 'skipped') {
                        $skipped++;
                        \Log::info("跳过重复数据", ['type' => $type, 'index' => $index]);
                    } else {
                        $failed++;
                        \Log::warning("保存失败", ['type' => $type, 'index' => $index]);
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'index' => $index,
                        'reason' => $e->getMessage()
                    ];
                    \Log::error('OpenClaw item save failed', [
                        'type' => $type,
                        'index' => $index,
                        'item' => array_keys($item),
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            DB::commit();

            \Log::info('===== OpenClaw 数据推送完成 =====', [
                'saved' => $saved,
                'skipped' => $skipped,
                'failed' => $failed
            ]);

            $durationMs = (int) max(0, now()->diffInMilliseconds($startedAt));
            $finalStatus = $failed > 0
                ? OpenclawTaskLog::STATUS_ERROR
                : (($saved === 0 && $skipped > 0) ? OpenclawTaskLog::STATUS_SKIPPED : OpenclawTaskLog::STATUS_SUCCESS);

            $taskLog->update([
                'status' => $finalStatus,
                'duration_ms' => $durationMs,
                'success_count' => $saved,
                'failed_count' => $failed,
                'skipped_count' => $skipped,
                'push_status' => OpenclawTaskLog::PUSH_SUCCESS,
                'push_response' => 'processed',
                'error_message' => $failed > 0 ? '部分数据保存失败' : null,
                'error_details' => $failed > 0 ? json_encode(array_slice($errors, 0, 10), JSON_UNESCAPED_UNICODE) : null,
                'finished_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "成功保存 {$saved} 条数据",
                'saved' => $saved,
                'skipped' => $skipped,
                'failed' => $failed,
                'errors' => array_slice($errors, 0, 10) // 只返回前 10 个错误
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('OpenClaw 数据推送失败 - 事务回滚', [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $durationMs = (int) max(0, now()->diffInMilliseconds($startedAt));
            $taskLog->update([
                'status' => OpenclawTaskLog::STATUS_ERROR,
                'duration_ms' => $durationMs,
                'push_status' => OpenclawTaskLog::PUSH_FAILED,
                'push_response' => 'exception',
                'error_message' => $e->getMessage(),
                'error_details' => substr($e->getTraceAsString(), 0, 8000),
                'finished_at' => now(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'errors' => [['reason' => $e->getMessage()]]
            ], 500);
        }
    }

    /**
     * 内容质量验证
     */
    private function validateContentQuality(string $type, array $item): array
    {
        switch ($type) {
            case 'articles':
                if (empty($item['source_url'])) {
                    return [
                        'passed' => false,
                        'message' => "缺少原始地址（source_url）"
                    ];
                }
                break;

            case 'projects':
                if (empty($item['url'])) {
                    return [
                        'passed' => false,
                        'message' => "缺少项目 URL"
                    ];
                }
                break;

            case 'side_hustle_cases':
                if (empty($item['resource_type']) || !in_array($item['resource_type'], ['article', 'video', 'disk', 'image'])) {
                    return [
                        'passed' => false,
                        'message' => "资源类型（resource_type）无效，必须是 article/video/disk/image"
                    ];
                }
                if (empty($item['resource_url'])) {
                    return [
                        'passed' => false,
                        'message' => "缺少原始资源地址（resource_url）"
                    ];
                }
                break;

            case 'ai_tool_monetization':
                if (empty($item['tool_url'])) {
                    return [
                        'passed' => false,
                        'message' => "缺少工具 URL"
                    ];
                }
                break;
        }

        return ['passed' => true, 'message' => '验证通过'];
    }

    /**
     * 保存文章
     */
    private function saveArticle($item)
    {
        // 检查是否已存在（通过 source_url）
        if (isset($item['url']) && Article::where('source_url', $item['url'])->exists()) {
            return 'skipped'; // 跳过重复内容
        }

        Article::create([
            'category_id' => null,
            'title' => $item['title'] ?? '无标题',
            'slug' => Str::slug($item['title'] ?? 'article') . '-' . time() . '-' . rand(1000, 9999),
            'summary' => $item['summary'] ?? null,
            'content' => $item['content'] ?? null,
            'cover_image' => $item['cover_image'] ?? null,
            'author_id' => null, // 可以设置为系统用户 ID
            'source_url' => $item['url'] ?? null,
            'is_vip' => $item['is_vip'] ?? 0,
            'is_published' => $item['is_published'] ?? 1,
            'published_at' => now()
        ]);

        return 'saved';
    }

    /**
     * 保存项目
     */
    private function saveProject($item)
    {
        // 检查是否已存在（通过 url）
        if (isset($item['url']) && Project::where('url', $item['url'])->exists()) {
            return 'skipped'; // 跳过重复内容
        }

        Project::create([
            'name' => $item['name'] ?? '无名称',
            'full_name' => $item['full_name'] ?? null,
            'description' => $item['description'] ?? null,
            'url' => $item['url'] ?? '',
            'language' => $item['language'] ?? null,
            'stars' => $item['stars'] ?? 0,
            'forks' => $item['forks'] ?? 0,
            'score' => $item['score'] ?? 0,
            'tags' => isset($item['tags']) ? json_encode($item['tags']) : null,
            'monetization' => null,
            'difficulty' => $item['difficulty'] ?? 'medium',
            'is_featured' => $item['is_featured'] ?? 0,
            'is_vip' => $item['is_vip'] ?? 0,
            'category_id' => null
        ]);

        return 'saved';
    }

    /**
     * 保存副业案例（支持多渠道资源）
     */
    private function saveSideHustleCase($item)
    {
        // 检查是否已存在（通过 resource_url）
        if (isset($item['resource_url']) && SideHustleCase::where('resource_url', $item['resource_url'])->exists()) {
            return 'skipped';
        }

        // 解析收入字符串（如"月收入 5000 元" → 5000）
        $estimatedIncome = 0;
        if (isset($item['income']) && preg_match('/(\d+)/', $item['income'], $matches)) {
            $estimatedIncome = (float) $matches[1];
        }

        // 解析难度到分类
        $difficultyMap = [
            'easy' => 'online',
            'medium' => 'online',
            'hard' => 'hybrid'
        ];
        $category = $difficultyMap[$item['difficulty'] ?? 'medium'] ?? 'online';

        // 解析资源类型到副业类型
        $resourceTypeMap = [
            'article' => 'content',
            'video' => 'content',
            'disk' => 'service',
            'image' => 'content'
        ];
        $type = $resourceTypeMap[$item['resource_type'] ?? 'article'] ?? 'content';

        SideHustleCase::create([
            'title' => $item['title'] ?? '无标题',
            'slug' => Str::slug($item['title'] ?? 'case') . '-' . time() . '-' . rand(1000, 9999),
            'summary' => $item['description'] ?? null,
            'content' => $item['content'] ?? null,
            'category' => $category,
            'type' => $type,
            'startup_cost' => '0',
            'time_investment' => '每天 2 小时',
            'resource_type' => $item['resource_type'] ?? 'article', // 新增字段
            'resource_url' => $item['resource_url'] ?? null,       // 新增字段
            'estimated_income' => $estimatedIncome,
            'actual_income' => null,
            'income_screenshots' => null,
            'steps' => $item['content'] ?? null, // 使用 content 作为步骤
            'tools' => isset($item['tags']) ? json_encode($item['tags']) : null,
            'pitfalls' => null,
            'willing_to_consult' => 0,
            'contact_info' => null,
            'visibility' => $item['is_vip'] ? 'vip' : 'public',
            'status' => 'approved', // 自动审核通过
            'audited_by' => null,
            'audited_at' => now(),
            'view_count' => 0,
            'like_count' => 0,
            'comment_count' => 0,
            'favorite_count' => 0,
            'user_id' => null
        ]);

        return 'saved';
    }

    /**
     * 保存 AI 工具变现（增强版）
     */
    private function saveAiToolMonetization($item)
    {
        // 检查是否已存在（通过 tool_url）
        if (isset($item['tool_url']) && AiToolMonetization::where('tool_url', $item['tool_url'])->exists()) {
            return 'skipped';
        }

        // 解析变现模式
        $pricingModelMap = [
            '订阅' => 'subscription',
            '按量' => 'pay_as_you_go',
            '一次性' => 'one_time',
            '免费 + 增值' => 'freemium',
            '免费' => 'free',
            'subscription' => 'subscription',
            'pay_as_you_go' => 'pay_as_you_go',
            'one_time' => 'one_time',
            'freemium' => 'freemium',
            'free' => 'free'
        ];
        $pricingModel = $pricingModelMap[$item['monetization_model'] ?? 'free'] ?? 'free';

        // 解析难度到分类
        $categoryMap = [
            'easy' => 'text',
            'medium' => 'text',
            'hard' => 'code'
        ];
        $category = $categoryMap[$item['difficulty'] ?? 'medium'] ?? 'text';

        AiToolMonetization::create([
            'tool_name' => $item['tool_name'] ?? '无名称',
            'slug' => Str::slug($item['tool_name'] ?? 'tool') . '-' . time() . '-' . rand(1000, 9999),
            'tool_url' => $item['tool_url'] ?? null,
            'category' => $category,
            'available_in_china' => 0, // 默认需要梯子
            'pricing_model' => $pricingModel,
            'content' => $item['description'] ?? null,
            'monetization_scenes' => isset($item['tags']) ? json_encode($item['tags']) : null,
            'prompt_templates' => null,
            'pricing_reference' => isset($item['pricing_details']) ? json_encode([['service' => '定价', 'price' => $item['pricing_details']]]) : null,
            'channels' => null,
            'delivery_standards' => null,
            'visibility' => $item['is_vip'] ? 'vip' : 'public',
            'view_count' => 0,
            'like_count' => 0,
            'favorite_count' => 0
        ]);

        return 'saved';
    }
}
