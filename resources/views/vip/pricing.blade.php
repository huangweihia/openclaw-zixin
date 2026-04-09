@extends('layouts.site')

@section('title', '价格方案 — ' . ($ocSite['site_name'] ?? 'OpenClaw 智信'))

@section('content')
    <div class="max-w-5xl mx-auto mb-10 text-center">
        <h1 class="text-3xl md:text-4xl font-bold oc-heading mb-2">选择适合你的方案</h1>
        <p class="text-sm oc-muted">所有价格均为演示数据，支付走演示闭环（非真实扣款）</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto items-stretch">
        @foreach ($plans as $key => $plan)
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
                    <a href="{{ route('payments.confirm', ['plan' => $key]) }}" class="btn btn-primary w-full text-sm vip-plan-cta justify-center">选择此方案</a>
                @else
                    <span class="text-xs oc-muted text-center block">无需购买</span>
                @endif
                </div>
            </div>
        @endforeach
    </div>
@endsection
