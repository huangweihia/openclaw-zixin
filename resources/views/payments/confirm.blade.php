@extends('layouts.site')

@section('title', '确认订单 — OpenClaw 智信')

@section('content')
    <div class="max-w-md mx-auto oc-surface p-8">
        <h1 class="text-xl font-bold oc-heading mb-2">确认订单</h1>
        <p class="text-sm oc-muted mb-6">请核对套餐后创建订单（演示环境）</p>

        <div class="rounded-lg p-4 mb-6" style="background: var(--light, #f1f5f9);">
            <p class="font-semibold oc-heading">{{ $plan['name'] }}</p>
            <p class="text-lg font-bold oc-heading mt-1">{{ $plan['price'] }}<span class="text-sm font-normal oc-muted">{{ $plan['period'] ?? '' }}</span></p>
        </div>

        @if ($payable)
            @auth
                <form method="post" action="{{ route('payments.orders.store') }}">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $planKey }}" />
                    <button type="submit" class="btn btn-primary w-full">创建订单</button>
                </form>
            @else
                <p class="text-sm oc-muted mb-4">登录后即可创建订单并完成演示支付。</p>
                <a href="{{ route('login', ['return' => '/payments/confirm?plan='.urlencode($planKey)]) }}" class="btn btn-primary w-full">登录后继续</a>
            @endauth
        @else
            <p class="text-sm oc-muted">当前方案无需下单。</p>
            <a href="{{ route('home') }}" class="btn btn-secondary w-full mt-4">返回首页</a>
        @endif

        <p class="text-center mt-6">
            <a href="{{ route('pricing') }}" class="oc-link text-sm">← 返回价格页</a>
        </p>
    </div>
@endsection
