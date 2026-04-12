<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Announcement;
use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\CommentReport;
use App\Models\EmailLog;
use App\Models\InvoiceRequest;
use App\Models\OpenclawTaskLog;
use App\Models\Order;
use App\Models\Project;
use App\Models\PublishAudit;
use App\Models\PushNotification;
use App\Models\RefundRequest;
use App\Models\SideHustleCase;
use App\Models\SvipCustomSubscription;
use App\Models\UserAction;
use App\Models\UserPost;
use App\Models\ViewHistory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $u = auth()->user();

        return $u instanceof User && $u->allowsAdminMenuKey('dashboard');
    }

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
        $paidCount = Order::query()->where('status', 'paid')->count();

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

        $articlesPublished = Article::query()->where('is_published', true)->count();
        $articlesDraft = Article::query()->where('is_published', false)->count();
        $articlesVip = Article::query()->where('is_vip', true)->count();

        $projectsCount = Schema::hasTable('projects') ? Project::query()->count() : 0;
        $casesCount = Schema::hasTable('side_hustle_cases') ? SideHustleCase::query()->count() : 0;
        $categoriesCount = Schema::hasTable('categories') ? Category::query()->count() : 0;

        $commentsTotal = Comment::query()->count();
        $commentsHidden = Comment::query()->where('is_hidden', true)->count();

        $viewHistory7d = Schema::hasTable('view_histories')
            ? ViewHistory::query()->where('viewed_at', '>=', now()->subDays(7))->count()
            : 0;

        $announcementsActive = Schema::hasTable('announcements')
            ? Announcement::query()
                ->where('is_published', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->count()
            : 0;

        $commentReportsPending = Schema::hasTable('comment_reports')
            ? CommentReport::query()->where('status', 'pending')->count()
            : 0;

        $invoicePending = Schema::hasTable('invoice_requests')
            ? InvoiceRequest::query()->where('status', 'pending')->count()
            : 0;

        $svipCustomPending = Schema::hasTable('svip_custom_subscriptions')
            ? SvipCustomSubscription::query()->where('status', 'pending')->count()
            : 0;

        $pushUnread = Schema::hasTable('push_notifications')
            ? PushNotification::query()->where('is_read', false)->count()
            : 0;

        $favoritesTotal = Schema::hasTable('user_actions')
            ? UserAction::query()->where('type', 'favorite')->count()
            : 0;

        $likesTotal = Schema::hasTable('user_actions')
            ? UserAction::query()->where('type', 'like')->count()
            : 0;

        return [
            Stat::make('注册用户', User::query()->count())
                ->description('今日新增 '.User::query()->whereDate('created_at', today())->count())
                ->icon('heroicon-o-users')
                ->color('success'),
            Stat::make('已发布文章', $articlesPublished)
                ->description('草稿 '.$articlesDraft.' · VIP 文 '.$articlesVip)
                ->icon('heroicon-o-document-text')
                ->color('info'),
            Stat::make('项目', $projectsCount)
                ->description('站内项目总数')
                ->icon('heroicon-o-cube')
                ->color('gray'),
            Stat::make('副业案例', $casesCount)
                ->description('案例库条目')
                ->icon('heroicon-o-briefcase')
                ->color('gray'),
            Stat::make('分类', $categoriesCount)
                ->description('文章分类数')
                ->icon('heroicon-o-tag')
                ->color('gray'),
            Stat::make('评论', $commentsTotal)
                ->description('已隐藏 '.$commentsHidden)
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('warning'),
            Stat::make('近7日浏览记录', $viewHistory7d)
                ->description('view_histories 写入量')
                ->icon('heroicon-o-eye')
                ->color('info'),
            Stat::make('待审动态', UserPost::query()->where('status', 'pending')->count())
                ->description('用户投稿待审核')
                ->icon('heroicon-o-newspaper')
                ->color('warning'),
            Stat::make('待审发布', $publishAuditPending)
                ->description('发布审计队列')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('warning'),
            Stat::make('待处理订单', Order::query()->where('status', 'pending')->count())
                ->description('未支付 / 待确认')
                ->icon('heroicon-o-shopping-cart')
                ->color('danger'),
            Stat::make('已付订单额', number_format($paidRevenue, 2))
                ->description('已付笔数 '.$paidCount)
                ->icon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('有效订阅', $subscriptionsActive)
                ->description('subscriptions 表中 status=active 且未过期')
                ->icon('heroicon-o-credit-card')
                ->color('success'),
            Stat::make('待处理退款', $refundPending)
                ->description('退款申请 pending')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger'),
            Stat::make('待开票', $invoicePending)
                ->description('发票申请 pending')
                ->icon('heroicon-o-receipt-percent')
                ->color('warning'),
            Stat::make('评论举报待处理', $commentReportsPending)
                ->description('status=pending')
                ->icon('heroicon-o-flag')
                ->color('danger'),
            Stat::make('SVIP 定制待处理', $svipCustomPending)
                ->description('用户提交的定制需求')
                ->icon('heroicon-o-sparkles')
                ->color('warning'),
            Stat::make('启用公告', $announcementsActive)
                ->description('当前启用的滚动公告条数')
                ->icon('heroicon-o-megaphone')
                ->color('info'),
            Stat::make('今日邮件', $emailsToday)
                ->description('按 sent_at 统计')
                ->icon('heroicon-o-envelope')
                ->color('gray'),
            Stat::make('未读推送', $pushUnread)
                ->description('站内 push_notifications')
                ->icon('heroicon-o-bell-alert')
                ->color('warning'),
            Stat::make('收藏总次', $favoritesTotal)
                ->description('点赞总次 '.$likesTotal)
                ->icon('heroicon-o-star')
                ->color('gray'),
            Stat::make('近7日任务异常', $openclawIssues)
                ->description('OpenClaw 失败/超时/有失败条数')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
