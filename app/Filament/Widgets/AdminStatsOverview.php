<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Comment;
use App\Models\EmailLog;
use App\Models\OpenclawTaskLog;
use App\Models\Order;
use App\Models\PublishAudit;
use App\Models\RefundRequest;
use App\Models\User;
use App\Models\UserPost;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $subscriptionsActive = 0;
        if (Schema::hasTable('subscriptions')) {
            $subscriptionsActive = (int) DB::table('subscriptions')
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->count();
        }

        $paidRevenue = (float) Order::query()->where('status', 'paid')->sum('amount');

        $publishAuditPending = PublishAudit::query()->where('status', 'pending')->count();

        $refundPending = 0;
        if (Schema::hasTable('refund_requests')) {
            $refundPending = RefundRequest::query()->where('status', 'pending')->count();
        }

        $emailsToday = 0;
        if (Schema::hasTable('email_logs')) {
            $emailsToday = EmailLog::query()->whereDate('sent_at', today())->count();
        }

        $openclawIssues = 0;
        if (Schema::hasTable('openclaw_task_logs')) {
            $openclawIssues = OpenclawTaskLog::query()
                ->where(function ($q) {
                    $q->whereIn('status', [OpenclawTaskLog::STATUS_ERROR, OpenclawTaskLog::STATUS_TIMEOUT])
                        ->orWhere('failed_count', '>', 0);
                })
                ->where('started_at', '>=', now()->subDays(7))
                ->count();
        }

        return [
            Stat::make('用户', User::query()->count())
                ->description('今日新增 '.User::query()->whereDate('created_at', today())->count()),
            Stat::make('文章', Article::query()->count())
                ->description('草稿 '.Article::query()->where('is_published', false)->count()),
            Stat::make('待审动态', UserPost::query()->where('status', 'pending')->count()),
            Stat::make('待审发布', $publishAuditPending)
                ->description('发布审计队列'),
            Stat::make('待处理订单', Order::query()->where('status', 'pending')->count()),
            Stat::make('待处理退款', $refundPending)
                ->description('退款申请'),
            Stat::make('已付订单额', number_format($paidRevenue, 2))
                ->description('已付笔数 '.Order::query()->where('status', 'paid')->count()),
            Stat::make('有效订阅', $subscriptionsActive),
            Stat::make('隐藏评论', Comment::query()->where('is_hidden', true)->count()),
            Stat::make('今日邮件', $emailsToday)
                ->description('已发送（按 sent_at）'),
            Stat::make('近7日任务异常', $openclawIssues)
                ->description('OpenClaw 失败/超时/有失败条数'),
        ];
    }
}
