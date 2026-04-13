@extends('layouts.site')

@section('title', '积分与充值 — OpenClaw 智信')

@section('content')
    @php
        $u = auth()->user();
    @endphp
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold oc-heading mb-2">⭐ 积分与充值</h1>
        <p class="text-sm oc-muted mb-6 m-0">积分可用于投稿加热（消耗积分提升曝光权重）。加热记录不在前台公示，相关触达通过站内消息与推送完成。</p>

        @if (session('success'))
            <div class="oc-flash oc-flash--success mb-4">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="oc-flash oc-flash--error mb-4">{{ session('error') }}</div>
        @endif

        <div class="oc-surface p-6 mb-6">
            <div class="text-sm oc-muted mb-1">当前余额</div>
            <div class="text-3xl font-bold oc-heading">{{ number_format((int) ($u->points_balance ?? 0)) }} <span class="text-base font-normal oc-muted">积分</span></div>
            <p class="text-xs oc-muted mt-3 m-0">单次加热约消耗 <strong class="oc-heading">{{ (int) $boostCost }}</strong> 积分（以系统配置为准）。</p>
        </div>

        <div class="oc-surface p-6 mb-6">
            <h2 class="text-lg font-bold oc-heading mb-4">积分规则说明</h2>
            <ul class="m-0 p-0 list-none space-y-2 text-sm">
                <li>每日首次登录奖励：<strong class="oc-heading">{{ (int) ($pointRules['daily_login'] ?? 0) }}</strong> 积分</li>
                <li>投稿审核通过：<strong class="oc-heading">{{ (int) ($pointRules['post_approved'] ?? 0) }}</strong> 积分</li>
                <li>投稿被点赞（作者）：<strong class="oc-heading">{{ (int) ($pointRules['post_liked_author'] ?? 0) }}</strong> 积分</li>
                <li>投稿被收藏（作者）：<strong class="oc-heading">{{ (int) ($pointRules['post_favorited_author'] ?? 0) }}</strong> 积分</li>
                <li>投稿被评论（作者）：<strong class="oc-heading">{{ (int) ($pointRules['post_commented_author'] ?? 0) }}</strong> 积分</li>
                <li>单次加热消耗：<strong class="oc-heading">{{ (int) ($pointRules['boost_cost'] ?? 0) }}</strong> 积分（有效 {{ (int) ($pointRules['boost_window_hours'] ?? 0) }} 小时）</li>
                <li>每次加热随机触达：<strong class="oc-heading">{{ (int) ($pointRules['boost_random_notify_users'] ?? 0) }}</strong> 位用户</li>
            </ul>
            <p class="text-xs oc-muted mt-4 m-0">
                当前规则来自服务器配置文件与环境变量（`config/points_rewards.php`、`config/boost.php`、`.env`）。
            </p>
        </div>

        <div class="oc-surface p-6">
            <h2 class="text-lg font-bold oc-heading mb-4">积分套餐</h2>
            @if ($packages->isEmpty())
                <p class="text-sm oc-muted m-0">暂无在售套餐，请稍后再试或联系管理员在后台配置。</p>
            @else
                <ul class="space-y-4 m-0 p-0 list-none">
                    @foreach ($packages as $pkg)
                        <li class="border oc-border rounded-lg p-4 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="font-semibold oc-heading">{{ $pkg->name ?? '套餐 #'.$pkg->id }}</div>
                                <div class="text-sm oc-muted mt-1">
                                    到账 <span class="font-semibold oc-heading">{{ number_format((int) $pkg->totalPointsNow()) }}</span> 积分
                                    @if ((int) ($pkg->bonus_points ?? 0) > 0)
                                        （含赠送 {{ number_format((int) $pkg->bonus_points) }}）
                                    @endif
                                    · ¥{{ number_format((float) $pkg->price_yuan, 2) }}
                                </div>
                                @if ($pkg->active_from || $pkg->active_until)
                                    <div class="text-xs oc-muted mt-1">
                                        有效期：
                                        {{ $pkg->active_from?->format('Y-m-d H:i') ?? '即刻' }}
                                        ~
                                        {{ $pkg->active_until?->format('Y-m-d H:i') ?? '长期' }}
                                    </div>
                                @endif
                            </div>
                            <form method="post" action="{{ route('dashboard.point-orders.store') }}" class="m-0">
                                @csrf
                                <input type="hidden" name="point_package_id" value="{{ $pkg->id }}" />
                                <button type="submit" class="btn btn-primary text-sm">去支付</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
                <p class="text-xs oc-muted mt-4 m-0">支付完成后将跳转结果页；演示环境可使用「模拟支付」完成入账。</p>
            @endif
        </div>
    </div>
@endsection
