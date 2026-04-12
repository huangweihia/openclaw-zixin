@extends('layouts.site')

@section('title', '价格方案 — ' . ($ocSite['site_name'] ?? 'OpenClaw 智信'))

@section('content')
    <div class="max-w-5xl mx-auto mb-10 text-center">
        <h1 class="text-3xl md:text-4xl font-bold oc-heading mb-2">选择适合你的方案</h1>
        <p class="text-sm oc-muted">所有价格均为演示数据，支付走演示闭环（非真实扣款）</p>
        @auth
            @if (auth()->user()->canAccessSvipExclusiveContent())
                <p class="text-sm mt-4 oc-heading font-semibold max-w-xl mx-auto">
                    您当前为 <strong>SVIP</strong>（或管理员），已包含 VIP 全部权益，无需再购买 VIP 档位。
                </p>
                <p class="text-sm mt-2 oc-muted">
                    <a href="{{ route('dashboard') }}" class="oc-link font-semibold">个人中心</a>
                    @if (auth()->user()->role === 'svip' || auth()->user()->isAdmin())
                        · <a href="{{ route('svip-subscriptions.index') }}" class="oc-link">SVIP 定制订阅</a>
                    @endif
                </p>
            @elseif (auth()->user()->canAccessVipExclusiveContent())
                <p class="text-sm mt-4 oc-muted max-w-xl mx-auto">您已开通 VIP，可继续升级 <strong>SVIP</strong> 解锁高阶权益。</p>
            @endif
        @endauth
    </div>

    <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto items-stretch">
        @foreach ($plans as $key => $plan)
            @php
                $hidePlan = false;
                if (auth()->check()) {
                    $hidePlan = auth()->user()->canAccessSvipExclusiveContent() && $key === 'vip';
                }
            @endphp
            @if ($hidePlan)
                @continue
            @endif
            <div id="plan-{{ $key }}" class="oc-surface p-6 flex flex-col h-full min-h-[380px] scroll-mt-24">
                <h2 class="text-lg font-bold oc-heading mb-2 shrink-0">{{ $plan['name'] }}</h2>
                @if (! empty($plan['promo_label']))
                    <p class="text-xs font-semibold text-orange-600 mb-2 m-0 shrink-0">{{ $plan['promo_label'] }}</p>
                @endif
                <div class="mb-3 shrink-0">
                    @if (! empty($plan['original_price']))
                        <p class="text-xs text-slate-400 line-through m-0 mb-1">原价 {{ $plan['original_price'] }}{{ $plan['period'] ?? '' }}</p>
                    @endif
                    <p class="text-2xl font-bold oc-heading m-0">{{ $plan['price'] }}<span class="text-sm font-normal oc-muted">{{ $plan['period'] ?? '' }}</span></p>
                </div>
                @include('partials.pricing-marketing', ['plan' => $plan])
                <ul class="text-sm oc-muted space-y-2 text-left flex-1 mb-6 list-none m-0 p-0">
                    @foreach ($plan['features'] as $f)
                        <li>· {{ $f }}</li>
                    @endforeach
                </ul>
                <div class="mt-auto shrink-0">
                @if (in_array($key, ['vip', 'svip'], true))
                    @if (auth()->check() && (($memberRole === 'vip' && $key === 'vip') || ($memberRole === 'svip' && $key === 'svip') || auth()->user()->isSiteSuperAdmin()))
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary w-full text-sm vip-plan-cta justify-center">已拥有该权益，去个人中心</a>
                    @else
                        <a href="{{ route('payments.confirm', ['plan' => $key]) }}" class="btn btn-primary w-full text-sm vip-plan-cta justify-center">选择此方案</a>
                    @endif
                @else
                    <span class="text-xs oc-muted text-center block">无需购买</span>
                @endif
                </div>
            </div>
        @endforeach
    </div>
@endsection
