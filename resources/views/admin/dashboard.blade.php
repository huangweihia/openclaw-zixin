@extends('layouts.admin')

@section('title', '仪表盘')

@section('content')
    <h1 class="admin-page-title">仪表盘</h1>
    <p class="admin-page-lead">快捷入口与待办概览。</p>

    <div class="admin-stat-grid">
        <a href="{{ route('admin.user-posts.index') }}" class="admin-stat-card">
            <span class="admin-stat-card__label">待审核投稿</span>
            <span class="admin-stat-card__value">{{ $pendingPostsCount }}</span>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="admin-stat-card">
            <span class="admin-stat-card__label">待支付订单</span>
            <span class="admin-stat-card__value">{{ $pendingOrdersCount }}</span>
        </a>
        <div class="admin-stat-card admin-stat-card--muted">
            <span class="admin-stat-card__label">用户反馈</span>
            <span class="admin-stat-card__value">—</span>
            <span class="admin-stat-card__hint">反馈表未接入，占位</span>
        </div>
    </div>

    <section class="admin-panel">
        <h2 class="admin-panel__title">待审核队列（最近 5 条）</h2>
        @forelse ($recentPendingPosts as $post)
            <article class="admin-queue-item">
                <div>
                    <a href="{{ route('admin.user-posts.show', $post) }}" class="admin-queue-item__title">{{ $post->title }}</a>
                    <p class="admin-queue-item__meta">
                        {{ $post->author?->name ?? '—' }} · {{ $post->created_at->format('Y-m-d H:i') }}
                    </p>
                </div>
                <div class="admin-queue-item__actions">
                    <a href="{{ route('admin.user-posts.show', $post) }}" class="btn btn-secondary btn--sm">详情</a>
                    <form method="post" action="{{ route('admin.user-posts.approve', $post) }}" class="admin-inline-form">
                        @csrf
                        <button type="submit" class="btn btn-primary btn--sm">通过</button>
                    </form>
                    <form method="post" action="{{ route('admin.user-posts.reject', $post) }}" class="admin-inline-form admin-reject-form--compact">
                        @csrf
                        <input type="text" name="audit_note" required placeholder="拒绝原因" class="admin-input-inline" maxlength="500">
                        <button type="submit" class="btn btn-secondary btn--sm">拒绝</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="admin-muted">暂无待审核投稿。</p>
        @endforelse
        <p class="admin-panel__footer">
            <a href="{{ route('admin.user-posts.index') }}">查看全部待审 →</a>
        </p>
    </section>
@endsection
