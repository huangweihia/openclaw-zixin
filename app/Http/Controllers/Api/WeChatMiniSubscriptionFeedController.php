<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\SvipSubscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

/**
 * 当前用户咨询订阅产出时间线：仅包含关联到本人 SVIP 订阅的文章（OpenClaw 回写入库）。
 */
class WeChatMiniSubscriptionFeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! Schema::hasTable('articles') || ! Schema::hasColumn('articles', 'svip_subscription_id')) {
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 15,
                    'total' => 0,
                    'note' => 'schema_pending',
                ],
            ]);
        }

        $subIds = SvipSubscription::query()
            ->where('user_id', $user->id)
            ->pluck('id');

        if ($subIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => min(max((int) $request->input('per_page', 20), 1), 50),
                    'total' => 0,
                    'note' => 'no_subscriptions',
                ],
            ]);
        }

        $perPage = min(max((int) $request->input('per_page', 20), 1), 50);

        $query = Article::query()
            ->where('is_published', true)
            ->whereNotNull('svip_subscription_id')
            ->whereIn('svip_subscription_id', $subIds)
            ->with(['svipSubscription:id,name'])
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage);

        $data = collect($paginator->items())->map(function (Article $a) {
            $sub = $a->svipSubscription;

            return [
                'type' => 'article',
                'id' => $a->id,
                'title' => $a->title,
                'slug' => $a->slug,
                'summary' => $a->summary,
                'cover_image' => $this->absoluteMediaUrl($a->cover_image),
                'view_count' => $a->view_count,
                'is_vip' => (bool) $a->is_vip,
                'published_at' => $a->published_at?->toIso8601String(),
                'subscription' => $sub ? [
                    'id' => $sub->id,
                    'name' => $sub->name,
                ] : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    private function absoluteMediaUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return url($path);
    }
}
