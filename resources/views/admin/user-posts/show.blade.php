@extends('layouts.admin')

@section('title', '投稿详情')

@section('content')
    <h1 class="admin-page-title">投稿详情</h1>
    <p class="admin-back">
        <a href="{{ route('admin.user-posts.index') }}">← 返回审核列表</a>
        <a href="{{ route('admin.dashboard') }}">仪表盘</a>
    </p>

    <article class="admin-detail">
        <header class="admin-detail__head">
            <h2 class="admin-detail__title">{{ $post->title }}</h2>
            <p class="admin-detail__meta">
                作者：{{ $post->author?->name ?? '—' }}（ID {{ $post->user_id }}）<br>
                类型 {{ $post->type }} · 可见性 {{ $post->visibility }} · 状态 <strong>{{ $post->status }}</strong><br>
                提交于 {{ $post->created_at->format('Y-m-d H:i:s') }}
                @if ($post->audited_at)
                    <br>审核于 {{ $post->audited_at->format('Y-m-d H:i:s') }}
                @endif
            </p>
        </header>
        <pre class="admin-detail__body">{{ $post->content }}</pre>
        @if ($post->audit_note)
            <p class="admin-detail__note"><strong>审核备注：</strong>{{ $post->audit_note }}</p>
        @endif
    </article>

    @if ($post->status === 'pending')
        <div class="admin-post-actions" style="margin-top:24px;">
            <form method="post" action="{{ route('admin.user-posts.approve', $post) }}" style="display:inline;margin:0;">
                @csrf
                <button type="submit" class="btn btn-primary">通过</button>
            </form>
            <form method="post" action="{{ route('admin.user-posts.reject', $post) }}" class="admin-reject-form">
                @csrf
                <label class="oc-label" for="audit-note">拒绝原因</label>
                <textarea id="audit-note" name="audit_note" required minlength="2" maxlength="500" rows="3" placeholder="请填写原因"></textarea>
                <button type="submit" class="btn btn-secondary">拒绝</button>
            </form>
        </div>
    @endif
@endsection
