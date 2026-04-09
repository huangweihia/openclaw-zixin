<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SvipContentController extends Controller
{
    /**
     * 获取 SVIP 专属内容
     * 
     * 返回付费用户专属的高质量内容，包括：
     * - 精选文章
     * - 副业实战案例
     * - AI 工具变现地图
     * - 私域流量 SOP
     * - 付费资源合集
     */
    public function index(Request $request)
    {
        // 验证 SVIP Token
        $token = $request->header('X-SVIP-Token');
        $expectedToken = env('SVIP_CONTENT_TOKEN', 'svip-fetch-token-2026');
        
        if ($token !== $expectedToken) {
            Log::warning("⚠️ SVIP Token 认证失败：" . ($token ?? 'empty'));
            return response()->json([
                'success' => false,
                'message' => '认证失败',
                'items' => [],
                'count' => 0
            ], 401);
        }
        
        Log::info("🔓 SVIP 内容请求 - Token 验证通过");
        
        $items = [];
        
        try {
            // 1. 获取 SVIP 专属文章 (如果有 articles 表)
            if ($this->tableExists('articles')) {
                $articles = DB::table('articles')
                    ->where('is_vip_only', true)
                    ->where('is_published', true)
                    ->orderBy('published_at', 'desc')
                    ->limit(10)
                    ->get();
                
                foreach ($articles as $article) {
                    $items[] = [
                        'type' => 'article',
                        'title' => $article->title ?? '无标题',
                        'content' => $article->summary ?? substr($article->content ?? '', 0, 500),
                        'url' => $article->source_url ?? url('/articles/' . ($article->slug ?? $article->id)),
                        'vip_only' => true,
                        'source' => '精选文章',
                        'created_at' => $article->published_at ?? $article->created_at ?? null
                    ];
                }
            }
            
            // 2. 获取副业案例 (如果有 side_hustle_cases 表)
            if ($this->tableExists('side_hustle_cases')) {
                $cases = DB::table('side_hustle_cases')
                    ->where('is_vip_only', true)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                
                foreach ($cases as $case) {
                    $items[] = [
                        'type' => 'project',
                        'name' => $case->title ?? '无标题',
                        'description' => $case->summary ?? substr($case->content ?? '', 0, 300),
                        'url' => url('/side-hustle/' . ($case->id ?? '#')),
                        'vip_only' => true,
                        'source' => '副业案例',
                        'estimated_income' => $case->estimated_monthly_income ?? null,
                        'created_at' => $case->created_at ?? null
                    ];
                }
            }
            
            // 3. 获取 AI 工具变现 (如果有 ai_tool_monetizations 表)
            if ($this->tableExists('ai_tool_monetizations')) {
                $tools = DB::table('ai_tool_monetizations')
                    ->where('is_vip_only', true)
                    ->orderBy('popularity_score', 'desc')
                    ->limit(5)
                    ->get();
                
                foreach ($tools as $tool) {
                    $items[] = [
                        'type' => 'resource',
                        'name' => $tool->tool_name ?? '未知工具',
                        'description' => $tool->description ?? 'AI 工具变现指南',
                        'url' => $tool->tool_url ?? '#',
                        'vip_only' => true,
                        'source' => 'AI 工具变现',
                        'category' => $tool->category ?? 'text',
                        'created_at' => $tool->created_at ?? null
                    ];
                }
            }
            
            // 4. 获取私域 SOP (如果有 private_traffic_sops 表)
            if ($this->tableExists('private_traffic_sops')) {
                $sops = DB::table('private_traffic_sops')
                    ->where('is_vip_only', true)
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
                    ->get();
                
                foreach ($sops as $sop) {
                    $items[] = [
                        'type' => 'knowledge',
                        'title' => $sop->title ?? '无标题',
                        'content' => $sop->summary ?? substr($sop->content ?? '', 0, 300),
                        'url' => url('/private-traffic/' . ($sop->id ?? '#')),
                        'vip_only' => true,
                        'source' => '私域 SOP',
                        'platform' => $sop->platform ?? 'wechat',
                        'created_at' => $sop->created_at ?? null
                    ];
                }
            }
            
            // 5. 获取付费资源 (如果有 premium_resources 表)
            if ($this->tableExists('premium_resources')) {
                $resources = DB::table('premium_resources')
                    ->where('is_vip_only', true)
                    ->orderBy('quality_score', 'desc')
                    ->limit(5)
                    ->get();
                
                foreach ($resources as $resource) {
                    $items[] = [
                        'type' => 'resource',
                        'title' => $resource->title ?? '无标题',
                        'content' => $resource->description ?? substr($resource->content ?? '', 0, 300),
                        'url' => url('/premium-resources/' . ($resource->id ?? '#')),
                        'vip_only' => true,
                        'source' => '付费资源',
                        'quality_score' => $resource->quality_score ?? 5,
                        'created_at' => $resource->created_at ?? null
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error("❌ SVIP 内容获取失败：" . $e->getMessage());
            // Return empty items on error, don't fail completely
        }
        
        // 如果没有 SVIP 内容，返回空数组（这是正常的）
        if (empty($items)) {
            Log::info("ℹ️ 暂无 SVIP 内容（空数组是正常的）");
        }
        
        // 随机打乱顺序
        shuffle($items);
        
        Log::info("✅ SVIP 内容返回成功，数量：" . count($items));
        
        return response()->json([
            'success' => true,
            'items' => $items,
            'count' => count($items),
            'timestamp' => now()->toIso8601String()
        ]);
    }
    
    /**
     * 检查数据库表是否存在
     */
    protected function tableExists(string $table): bool
    {
        try {
            DB::select("SELECT 1 FROM {$table} LIMIT 1");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
