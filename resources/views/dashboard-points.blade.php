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
                                    到账 <span class="font-semibold oc-heading">{{ number_format((int) $pkg->points_amount) }}</span> 积分
                                    · ¥{{ number_format((float) $pkg->price_yuan, 2) }}
                                </div>
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
