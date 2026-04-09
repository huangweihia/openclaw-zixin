<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Order;
use App\Models\Project;
use App\Models\User;
use App\Models\UserPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        try {
            return response()->json($this->buildPayload());
        } catch (Throwable $e) {
            Log::error('admin.dashboard.stats', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'summary' => $this->emptySummary(),
                'todo' => [
                    'moderation' => 0,
                    'orders' => 0,
                    'draft_articles' => 0,
                    'hidden_comments' => 0,
                ],
                'recent_pending_posts' => [],
                'recent_pending_orders' => [],
                'runtime' => $this->runtimeBlock(),
                'degraded' => true,
                'load_error' => config('app.debug') ? $e->getMessage() : '统计数据暂时不可用，请确认已执行数据库迁移且连接正常。',
            ]);
        }
    }

    private function buildPayload(): array
    {
        $usersByRole = User::query()
            ->selectRaw('role, COUNT(*) as c')
            ->groupBy('role')
            ->pluck('c', 'role')
            ->all();

        $recentPending = UserPost::query()
            ->where('status', 'pending')
            ->latest()
            ->with('author:id,name,email')
            ->limit(6)
            ->get()
            ->map(fn (UserPost $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'type' => $p->type,
                'created_at' => $p->created_at?->toIso8601String(),
                'author' => $p->author ? [
                    'id' => $p->author->id,
                    'name' => $p->author->name,
                    'email' => $p->author->email,
                ] : null,
            ]);

        $recentPendingOrders = Order::query()
            ->where('status', 'pending')
            ->latest()
            ->with('user:id,name,email')
            ->limit(5)
            ->get()
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'order_no' => $o->order_no,
                'amount' => $o->amount,
                'product_type' => $o->product_type,
                'created_at' => $o->created_at?->toIso8601String(),
                'user' => $o->user,
            ]);

        $paidRevenue = (float) Order::query()->where('status', 'paid')->sum('amount');

        $subscriptionsActive = 0;
        if (Schema::hasTable('subscriptions')) {
            $subscriptionsActive = DB::table('subscriptions')
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->count();
        }

        $usersBanned = 0;
        if (Schema::hasColumn('users', 'is_banned')) {
            $usersBanned = User::query()->where('is_banned', true)->count();
        }

        $summary = [
            'users_total' => User::query()->count(),
            'users_new_today' => User::query()->whereDate('created_at', today())->count(),
            'users_banned' => $usersBanned,
            'users_by_role' => $usersByRole,
            'articles_total' => Article::query()->count(),
            'articles_published' => Article::query()->where('is_published', true)->count(),
            'articles_draft' => Article::query()->where('is_published', false)->count(),
            'articles_vip' => Article::query()->where('is_vip', true)->count(),
            'projects_total' => Project::query()->count(),
            'projects_featured' => Project::query()->where('is_featured', true)->count(),
            'categories_total' => Category::query()->count(),
            'comments_total' => Comment::query()->count(),
            'comments_hidden' => Comment::query()->where('is_hidden', true)->count(),
            'user_posts_pending' => UserPost::query()->where('status', 'pending')->count(),
            'user_posts_approved' => UserPost::query()->where('status', 'approved')->count(),
            'user_posts_rejected' => UserPost::query()->where('status', 'rejected')->count(),
            'orders_pending' => Order::query()->where('status', 'pending')->count(),
            'orders_paid_count' => Order::query()->where('status', 'paid')->count(),
            'orders_paid_revenue' => $paidRevenue,
            'subscriptions_active' => $subscriptionsActive,
        ];

        $todo = [
            'moderation' => $summary['user_posts_pending'],
            'orders' => $summary['orders_pending'],
            'draft_articles' => $summary['articles_draft'],
            'hidden_comments' => $summary['comments_hidden'],
        ];

        return [
            'summary' => $summary,
            'todo' => $todo,
            'recent_pending_posts' => $recentPending,
            'recent_pending_orders' => $recentPendingOrders,
            'runtime' => $this->runtimeBlock(),
        ];
    }

    private function emptySummary(): array
    {
        return [
            'users_total' => 0,
            'users_new_today' => 0,
            'users_banned' => 0,
            'users_by_role' => [],
            'articles_total' => 0,
            'articles_published' => 0,
            'articles_draft' => 0,
            'articles_vip' => 0,
            'projects_total' => 0,
            'projects_featured' => 0,
            'categories_total' => 0,
            'comments_total' => 0,
            'comments_hidden' => 0,
            'user_posts_pending' => 0,
            'user_posts_approved' => 0,
            'user_posts_rejected' => 0,
            'orders_pending' => 0,
            'orders_paid_count' => 0,
            'orders_paid_revenue' => 0.0,
            'subscriptions_active' => 0,
        ];
    }

    private function runtimeBlock(): array
    {
        return [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'docker_compose_hint' => '本地默认端口：Web nginx 8083 → 容器 80；MySQL 3309 → 3306；应用代码挂载 ./_laravel_temp。',
        ];
    }
}
