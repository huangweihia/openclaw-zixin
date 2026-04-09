@extends('layouts.admin')

@section('title', '订单管理')

@section('content')
    <h1 class="admin-page-title">订单管理</h1>
    <p class="admin-page-lead">按状态筛选，默认展示全部。</p>

    <div class="admin-tabs">
        <a href="{{ route('admin.orders.index') }}" class="admin-tabs__item @if(empty($status)) is-active @endif">全部</a>
        @foreach (['pending' => '待支付', 'paid' => '已支付', 'failed' => '失败', 'refunded' => '已退款'] as $st => $label)
            <a href="{{ route('admin.orders.index', ['status' => $st]) }}" class="admin-tabs__item @if(($status ?? '') === $st) is-active @endif">{{ $label }}</a>
        @endforeach
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>订单号</th>
                    <th>用户</th>
                    <th>商品</th>
                    <th>金额</th>
                    <th>状态</th>
                    <th>创建时间</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td><code>{{ $order->order_no }}</code></td>
                        <td>{{ $order->user?->name ?? '—' }}</td>
                        <td>{{ $order->product_type }} #{{ $order->product_id }}</td>
                        <td>¥{{ $order->amount }}</td>
                        <td><span class="admin-badge">{{ $order->status }}</span></td>
                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="admin-muted">暂无订单。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $orders->links() }}
    </div>
@endsection
