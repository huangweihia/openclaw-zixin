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
                                <details class="mt-3">
                                    <summary class="text-sm oc-link" style="cursor:pointer;">评价此订单（进入首页评价池）</summary>
                                    <form method="post" action="{{ route('dashboard.orders.review', $o) }}" class="mt-3">
                                        @csrf
                                        <div class="flex flex-wrap gap-3 items-center mb-2">
                                            <label class="text-sm oc-muted">评分</label>
                                            <select name="rating" class="oc-input" style="max-width:120px;">
                                                @for ($i = 5; $i >= 1; $i--)
                                                    <option value="{{ $i }}">{{ $i }} 星</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <textarea name="body" rows="3" class="oc-input w-full" maxlength="5000" required placeholder="写下你的真实反馈（审核通过后会展示在首页用户评价区）"></textarea>
                                        <div class="mt-2">
                                            <button type="submit" class="btn btn-secondary text-sm">提交评价</button>
                                        </div>
                                    </form>
                                </details>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
@endsection

