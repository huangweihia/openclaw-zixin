<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Order;
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

        return [
            Stat::make('用户', User::query()->count())
                ->description('今日新增 '.User::query()->whereDate('created_at', today())->count()),
            Stat::make('文章', Article::query()->count())
                ->description('草稿 '.Article::query()->where('is_published', false)->count()),
            Stat::make('待审动态', UserPost::query()->where('status', 'pending')->count()),
            Stat::make('待处理订单', Order::query()->where('status', 'pending')->count()),
            Stat::make('已付订单额', number_format($paidRevenue, 2))
                ->description('已付笔数 '.Order::query()->where('status', 'paid')->count()),
            Stat::make('有效订阅', $subscriptionsActive),
            Stat::make('隐藏评论', Comment::query()->where('is_hidden', true)->count()),
        ];
    }
}
