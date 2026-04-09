@extends('layouts.site')

@section('title', 'VIP 会员 — OpenClaw 智信')

@section('content')
    <div class="max-w-4xl mx-auto text-center mb-12">
        <h1 class="text-3xl md:text-4xl font-bold oc-heading mb-3">VIP 会员权益</h1>
        <p class="text-sm oc-muted">解锁全文阅读、专属标识与更多增值服务</p>
        @auth
            @if (auth()->user()->hasMemberMenuPrivileges())
                <p class="text-sm mt-4 oc-heading font-semibold">您已享有会员或管理员权益，可前往 <a href="{{ route('dashboard') }}" class="oc-link">个人中心</a> 查看。</p>
                @if (auth()->user()->role === 'svip' || auth()->user()->isAdmin())
                    <p class="text-sm mt-2 oc-muted">SVIP 定制订阅：<a href="{{ route('svip-subscriptions.index') }}" class="oc-link">提交与管理申请</a>（管理员可进入验收）</p>
                @endif
            @endif
        @endauth
    </div>

    @auth
        <div class="max-w-5xl mx-auto mb-8">
            <div class="oc-surface p-6">
                <h2 class="text-lg font-bold oc-heading mb-2">当前权益</h2>
                <div class="text-sm oc-muted">
                    @if ($memberRole === 'admin')
                        超级管理员：拥有全站所有权限（含 VIP/SVIP）。
                    @elseif ($memberRole === 'svip')
                        当前身份：SVIP
                    @elseif ($memberRole === 'vip')
                        当前身份：VIP
                    @else
                        当前身份：普通用户（未开通会员）
                    @endif
                </div>
                @if ($memberExpiresAt)
                    <p class="text-sm mt-2 oc-heading">
                        权益到期时间：{{ $memberExpiresAt->format('Y-m-d H:i') }}
                        @if ($memberDaysLeft !== null)
                            （剩余 {{ $memberDaysLeft }} 天）
                        @endif
                    </p>
                @endif
            </div>
        </div>
    @endauth

    <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto mb-12 items-stretch">
        @foreach ($plans as $key => $plan)
            @php
                $hidePlan = false;
                if (auth()->check()) {
                    $hidePlan = ($memberRole === 'svip' || $memberRole === 'admin') && $key === 'vip';
                }
            @endphp
            @if ($hidePlan)
                @continue
            @endif
            <div class="oc-surface p-6 flex flex-col h-full min-h-[360px] {{ $key === 'vip' ? 'vip-plan-highlight' : '' }}">
                <h2 class="text-lg font-bold oc-heading mb-2 shrink-0">{{ $plan['name'] }}</h2>
                @if (! empty($plan['promo_label']))
                    <p class="text-xs font-semibold text-orange-600 mb-2 m-0 shrink-0">{{ $plan['promo_label'] }}</p>
                @endif
                <div class="shrink-0 mb-3">
                    @if (! empty($plan['original_price']))
                        <p class="text-xs text-slate-400 line-through m-0 mb-1">原价 {{ $plan['original_price'] }}{{ $plan['period'] ?? '' }}</p>
                    @endif
                    <p class="text-2xl font-bold oc-heading m-0">{{ $plan['price'] }}<span class="text-sm font-normal oc-muted">{{ $plan['period'] ?? '' }}</span></p>
                </div>
                @include('partials.pricing-marketing', ['plan' => $plan])
                <ul class="text-sm oc-muted space-y-2 text-left flex-1 mb-6 list-none m-0 p-0">
                    @foreach ($plan['features'] as $f)
                        <li class="flex gap-2"><span class="text-primary shrink-0" style="color: var(--primary);">✓</span> {{ $f }}</li>
                    @endforeach
                </ul>
                <div class="mt-auto shrink-0">
                @if (in_array($key, ['vip', 'svip'], true))
                    @if (auth()->check() && (($memberRole === 'vip' && $key === 'vip') || ($memberRole === 'svip' && $key === 'svip') || $memberRole === 'admin'))
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary w-full text-sm vip-plan-cta">已拥有该权益，去个人中心</a>
                    @else
                        <a href="{{ route('pricing') }}#plan-{{ $key }}" class="btn btn-primary w-full text-sm vip-plan-cta">查看方案并开通</a>
                    @endif
                @else
                    <span class="text-xs oc-muted block text-center">默认权益，注册即用</span>
                @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="max-w-5xl mx-auto mb-12">
        @include('partials.membership-compare')
    </div>

    <div class="text-center">
        <a href="{{ route('pricing') }}" class="btn btn-secondary text-sm">打开价格对比页</a>
    </div>
@endsection
