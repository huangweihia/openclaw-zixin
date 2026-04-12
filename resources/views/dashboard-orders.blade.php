@extends('layouts.site')

@section('title', '我的订单 — OpenClaw 智信')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold oc-heading mb-6">💳 我的订单</h1>

        @if (session('success'))
            <div class="oc-flash oc-flash--success mb-4">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="oc-flash oc-flash--error mb-4">{{ session('error') }}</div>
        @endif

        <div class="oc-surface p-6">
            @if ($orders->isEmpty())
                <p class="text-sm oc-muted m-0">暂无订单记录。</p>
            @else
                <div class="space-y-4">
                    @foreach ($orders as $o)
                        @php
                            $plan = $o->planKeyFromProduct();
                            $planLabel = $plan ? strtoupper($plan) : '—';
                            $paidAt = $o->paid_at ?? $o->created_at;
                            $withinRefundWindow = $o->status === 'paid'
                                && $paidAt
                                && $paidAt->gte(now()->subDays(7));
                        @endphp
                        <div class="border-b oc-divide pb-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm oc-muted">订单号：<span class="font-mono oc-heading">{{ $o->order_no }}</span></div>
                                    <div class="text-sm oc-muted mt-1">
                                        套餐：<span class="oc-heading font-semibold">{{ $planLabel }}</span>
                                        · 金额：<span class="oc-heading font-semibold">¥{{ number_format((float) $o->amount, 2) }}</span>
                                        · 状态：<span class="oc-heading font-semibold">{{ $o->status }}</span>
                                    </div>
                                </div>
                                <div class="text-xs oc-muted">
                                    {{ $o->created_at?->format('Y-m-d H:i') }}
                                </div>
                            </div>

                            @if ($o->status === 'paid')
                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    @if ($o->refund_requested_at)
                                        <span class="text-sm oc-muted">退款申请已提交（{{ $o->refund_requested_at->format('Y-m-d H:i') }}）</span>
                                    @elseif ($withinRefundWindow)
                                        <form method="post" action="{{ route('dashboard.orders.refund', $o) }}" class="m-0" onsubmit="return confirm('确认提交退款申请？提交后请等待客服处理。');">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary text-sm">申请退款（7 日内）</button>
                                        </form>
                                    @else
                                        <span class="text-xs oc-muted">已超过 7 日退款申请期</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">{{ $orders->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
@endsection
