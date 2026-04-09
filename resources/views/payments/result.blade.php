@extends('layouts.site')

@section('title', '支付结果 — OpenClaw 智信')

@section('content')
    <div class="max-w-md mx-auto oc-surface p-8 text-center">
        <h1 class="text-xl font-bold oc-heading mb-2">订单结果</h1>
        <p class="text-sm oc-muted mb-6">订单号：<span class="font-mono oc-heading">{{ $order->order_no }}</span></p>

        <div class="rounded-lg p-4 mb-6 text-left" style="background: var(--light, #f1f5f9);">
            <p class="text-sm oc-muted">状态</p>
            <p class="font-semibold oc-heading">{{ $order->status === 'paid' ? '已支付' : '待支付' }}</p>
            @if ($planKey)
                <p class="text-sm oc-muted mt-2">套餐</p>
                <p class="font-semibold oc-heading">{{ strtoupper($planKey) }}</p>
            @endif
            <p class="text-sm oc-muted mt-2">金额</p>
            <p class="font-semibold oc-heading">¥{{ number_format((float) $order->amount, 2) }}</p>
        </div>

        @if (session('success'))
            <div class="oc-flash oc-flash--success mb-4 text-left">{{ session('success') }}</div>
        @endif

        @if ($simulateEnabled && $order->status === 'pending')
            <form method="post" action="{{ route('payments.simulate-paid', $order) }}" class="mb-4">
                @csrf
                <button type="submit" class="btn btn-primary w-full">演示：标记为已支付</button>
            </form>
            <p class="text-xs oc-muted">本地/演示环境可用，模拟开通会员（非真实扣款）。</p>
        @endif

        <div class="flex flex-col gap-2 mt-8">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary w-full">个人中心</a>
            <a href="{{ route('home') }}" class="oc-link text-sm">返回首页</a>
        </div>
    </div>
@endsection
