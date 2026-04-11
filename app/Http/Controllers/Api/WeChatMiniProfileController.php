<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Order;
use App\Models\Project;
use App\Models\SideHustleCase;
use App\Models\UserAction;
use App\Models\User;
use App\Models\UserPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * 小程序「我的」原生数据：订单、收藏、帖子列表（Sanctum）。
 */
class WeChatMiniProfileController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        return response()->json([
            'data' => self::statsFor($request->user()),
        ]);
    }

    /**
     * @return array{orders_count: int, favorites_count: int, posts_count: int}
     */
    public static function statsFor(User $user): array
    {
        $uid = (int) $user->id;

        $ordersCount = Schema::hasTable('orders')
            ? (int) Order::query()->where('user_id', $uid)->count()
            : 0;

        $favoritesCount = Schema::hasTable('user_actions')
            ? (int) UserAction::query()->where('user_id', $uid)->where('type', 'favorite')->count()
            : 0;

        $postsCount = 0;
        if (Schema::hasTable('user_posts')) {
            $postsCount += (int) UserPost::query()->where('user_id', $uid)->where('status', 'approved')->count();
        }
        if (Schema::hasTable('articles')) {
            $postsCount += (int) Article::query()->where('author_id', $uid)->where('is_published', true)->count();
        }

        return [
            'orders_count' => $ordersCount,
            'favorites_count' => $favoritesCount,
            'posts_count' => $postsCount,
        ];
    }

    public function orders(Request $request): JsonResponse
    {
        if (! Schema::hasTable('orders')) {
            return response()->json(['data' => []]);
        }
        $uid = (int) $request->user()->id;
        $rows = Order::query()
            ->where('user_id', $uid)
            ->orderByDesc('id')
            ->limit(80)
            ->get(['id', 'order_no', 'amount', 'status', 'product_type', 'created_at']);

        return response()->json([
            'data' => $rows->map(fn (Order $o) => [
                'id' => $o->id,
                'order_no' => $o->order_no,
                'amount' => (string) $o->amount,
                'status' => $o->status,
                'status_label' => $this->orderStatusLabel($o->status),
                'product_type' => $o->product_type,
                'created_at' => $o->created_at?->toIso8601String(),
            ]),
        ]);
    }

    public function favorites(Request $request): JsonResponse
    {
        if (! Schema::hasTable('user_actions')) {
            return response()->json(['data' => []]);
        }
        $uid = (int) $request->user()->id;
        $articleMorph = (new Article)->getMorphClass();
        $projectMorph = (new Project)->getMorphClass();
        $caseMorph = (new SideHustleCase)->getMorphClass();
        $postMorph = (new UserPost)->getMorphClass();

        $actions = UserAction::query()
            ->where('user_id', $uid)
            ->where('type', 'favorite')
            ->whereIn('actionable_type', [$articleMorph, $projectMorph, $caseMorph, $postMorph])
            ->orderByDesc('created_at')
            ->limit(60)
            ->get();

        $actions->loadMorph('actionable', [
            Article::class => [],
            Project::class => [],
            SideHustleCase::class => [],
            UserPost::class => [],
        ]);

        $out = [];
        foreach ($actions as $a) {
            $row = $this->favoriteRow($a);
            if ($row !== null) {
                $out[] = $row;
            }
        }

        return response()->json(['data' => $out]);
    }

    public function posts(Request $request): JsonResponse
    {
        if (! Schema::hasTable('user_posts')) {
            return response()->json(['data' => []]);
        }
        $uid = (int) $request->user()->id;
        $rows = UserPost::query()
            ->where('user_id', $uid)
            ->orderByDesc('id')
            ->limit(80)
            ->get(['id', 'title', 'status', 'view_count', 'created_at']);

        return response()->json([
            'data' => $rows->map(fn (UserPost $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'status' => $p->status,
                'status_label' => $this->postStatusLabel($p->status),
                'view_count' => (int) ($p->view_count ?? 0),
                'created_at' => $p->created_at?->toIso8601String(),
            ]),
        ]);
    }

    private function orderStatusLabel(?string $status): string
    {
        return match ($status) {
            'paid', 'completed' => '已支付',
            'pending' => '待支付',
            'failed' => '失败',
            'cancelled' => '已取消',
            'refunded' => '已退款',
            default => $status ?: '—',
        };
    }

    private function postStatusLabel(?string $status): string
    {
        return match ($status) {
            'approved' => '已通过',
            'pending' => '审核中',
            'rejected' => '未通过',
            default => $status ?: '—',
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    private function favoriteRow(UserAction $a): ?array
    {
        $m = $a->actionable;
        if ($m === null) {
            return null;
        }
        $title = match (true) {
            $m instanceof Article => (string) ($m->title ?? '文章'),
            $m instanceof UserPost => (string) ($m->title ?? '帖子'),
            $m instanceof Project => (string) ($m->name ?? '项目'),
            $m instanceof SideHustleCase => (string) ($m->title ?? '案例'),
            default => '收藏',
        };

        return [
            'id' => $a->id,
            'title' => $title,
            'kind' => class_basename($m),
            'created_at' => $a->created_at?->toIso8601String(),
        ];
    }
}
