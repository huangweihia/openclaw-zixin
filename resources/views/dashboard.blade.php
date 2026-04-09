@extends('layouts.site')

@section('title', '个人中心 — OpenClaw 智信')

@section('content')
    @php
        $u = auth()->user();
    @endphp

    <h1 class="text-3xl font-bold text-center mb-8 oc-heading">👤 个人中心</h1>

    {{-- 原型：用户信息卡片 + 编辑入口 + 统计 --}}
    <div class="oc-surface p-6 md:p-8 mb-8">
        <div class="flex flex-col sm:flex-row sm:items-start gap-6">
            <div class="shrink-0 mx-auto sm:mx-0 text-center sm:text-left">
                @if ($u->avatar)
                    <img src="{{ $u->avatar }}" alt="" class="w-20 h-20 md:w-24 md:h-24 rounded-full object-cover border-2 mx-auto sm:mx-0" style="border-color: rgba(148,163,184,.35);" />
                @else
                    <div class="w-20 h-20 md:w-24 md:h-24 rounded-full mx-auto sm:mx-0 flex items-center justify-center text-2xl font-bold text-white" style="background: var(--gradient-primary);">
                        {{ mb_substr($u->name, 0, 1) }}
                    </div>
                @endif
            </div>
            <div class="flex-1 min-w-0 text-center sm:text-left">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-2 w-full">
                    <h2 class="text-xl md:text-2xl font-bold oc-heading m-0">{{ $u->name }}</h2>
                    <a href="{{ route('dashboard.edit') }}" class="btn btn-secondary text-sm shrink-0">编辑</a>
                </div>
                <p class="text-sm oc-muted mb-1">{{ $handle }}</p>
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-3 gap-y-1 text-sm">
                    <span class="font-semibold oc-heading">{{ $roleLabel }}</span>
                    @if ($vipDays !== null)
                        <span class="oc-muted">剩余 <strong class="oc-heading">{{ $vipDays }}</strong> 天</span>
                    @elseif ($u->isVip() && $u->subscription_ends_at)
                        <span class="oc-muted">有效期至 {{ $u->subscription_ends_at->format('Y-m-d') }}</span>
                    @endif
                </div>
                @if ($u->bio)
                    <p class="text-sm oc-muted mt-3 line-clamp-2">{{ $u->bio }}</p>
                @endif
            </div>
        </div>

        <div class="mt-8 pt-6 border-t oc-border">
            <h3 class="text-sm font-bold oc-heading mb-4">📊 统计数据</h3>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="oc-stat-card rounded-xl p-4 text-center">
                    <div class="text-lg mb-1">📝</div>
                    <div class="text-xs oc-muted">发布数</div>
                    <div class="text-xl font-bold oc-heading">{{ number_format($postsCount) }}</div>
                </div>
                <div class="oc-stat-card rounded-xl p-4 text-center">
                    <div class="text-lg mb-1">👁️</div>
                    <div class="text-xs oc-muted">内容被浏览</div>
                    <div class="text-xl font-bold oc-heading">{{ number_format($viewsCount) }}</div>
                    <div class="text-[11px] oc-muted mt-1">足迹 {{ number_format($footprintCount) }} 条</div>
                </div>
                <div class="oc-stat-card rounded-xl p-4 text-center">
                    <div class="text-lg mb-1">⭐</div>
                    <div class="text-xs oc-muted">收藏数</div>
                    <div class="text-xl font-bold oc-heading">{{ number_format($favoritesCount) }}</div>
                </div>
                <div class="oc-stat-card rounded-xl p-4 text-center">
                    <div class="text-lg mb-1">💬</div>
                    <div class="text-xs oc-muted">评论数</div>
                    <div class="text-xl font-bold oc-heading">{{ number_format($commentsCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    @if (! $u->isAdmin())
        <div class="max-w-4xl mx-auto mb-8">
            @include('partials.membership-compare')
        </div>
    @endif

    {{-- 订阅（独立条，原型外补充） --}}
    <div class="oc-surface p-6 mb-8">
        <h3 class="font-bold oc-heading mb-3">订阅</h3>
        @if ($u->isAdmin())
            <p class="text-sm oc-muted mb-4">超级管理员享有全站权益，无需开通 VIP。</p>
            <a href="{{ route('pricing') }}" class="btn btn-secondary text-sm">查看价格页（演示）</a>
        @else
            <p class="text-sm oc-muted mb-4">续费或升级请在价格页完成支付。</p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('pricing') }}" class="btn btn-primary text-sm">价格与套餐</a>
                <a href="{{ route('payments.confirm', ['plan' => 'vip']) }}" class="btn btn-secondary text-sm">去支付页</a>
            </div>
        @endif
    </div>

    {{-- 快捷入口 --}}
    <div class="mb-8">
        <h3 class="text-sm font-bold oc-heading mb-4">快捷入口</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <a href="{{ route('user-posts.index') }}" class="oc-quick-tile flex flex-col items-center justify-center gap-2 p-5 rounded-xl text-center">
                <span class="text-2xl" aria-hidden="true">📝</span>
                <span class="text-sm font-semibold oc-heading">我的发布</span>
            </a>
            <a href="{{ route('favorites.index') }}" class="oc-quick-tile flex flex-col items-center justify-center gap-2 p-5 rounded-xl text-center">
                <span class="text-2xl" aria-hidden="true">⭐</span>
                <span class="text-sm font-semibold oc-heading">我的收藏</span>
            </a>
            <a href="{{ route('history.index') }}" class="oc-quick-tile flex flex-col items-center justify-center gap-2 p-5 rounded-xl text-center">
                <span class="text-2xl" aria-hidden="true">👣</span>
                <span class="text-sm font-semibold oc-heading">浏览历史</span>
            </a>
            <a href="{{ route('dashboard.comments') }}" class="oc-quick-tile flex flex-col items-center justify-center gap-2 p-5 rounded-xl text-center">
                <span class="text-2xl" aria-hidden="true">💬</span>
                <span class="text-sm font-semibold oc-heading">我的评论</span>
            </a>
            @if ($u->role === 'svip' || $u->isAdmin())
                <a href="{{ route('svip-subscriptions.index') }}" class="oc-quick-tile flex flex-col items-center justify-center gap-2 p-5 rounded-xl text-center">
                    <span class="text-2xl" aria-hidden="true">✨</span>
                    <span class="text-sm font-semibold oc-heading">SVIP 定制</span>
                </a>
            @endif
        </div>
    </div>

    {{-- 最近动态 --}}
    <div id="dash-timeline" class="oc-surface p-6 md:p-8">
        <h3 class="text-lg font-bold oc-heading mb-4">最近动态</h3>
        <p class="text-xs oc-muted mb-4">时间线</p>
        @if ($timeline->isEmpty())
            <p class="text-sm oc-muted m-0">暂无动态，去发布内容或参与互动吧。</p>
        @else
            <ul class="space-y-0 m-0 p-0 list-none border-l-2 pl-4 oc-timeline-border">
                @foreach ($timeline as $row)
                    <li class="relative pb-5 pl-2 oc-timeline-item">
                        <span class="absolute -left-[9px] top-1.5 w-2 h-2 rounded-full oc-timeline-dot" aria-hidden="true"></span>
                        <time class="text-xs oc-muted block mb-1">{{ $row['at']->format('Y-m-d H:i') }}</time>
                        <span class="text-sm oc-heading">{{ $row['text'] }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
