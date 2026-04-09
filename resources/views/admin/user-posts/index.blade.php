@extends('layouts.admin')

@section('title', '审核投稿')

@section('content')
    <h1 class="admin-page-title">待审核投稿</h1>
    <p class="admin-page-lead">通过或拒绝用户投稿；拒绝需填写原因。</p>

    <div class="admin-post-list">
        @forelse ($posts as $post)
            <article class="admin-post-card">
                <header class="admin-post-head">
                    <h2 class="admin-post-title">
                        <a href="{{ route('admin.user-posts.show', $post) }}">{{ $post->title }}</a>
                    </h2>
                    <p class="admin-post-meta">
                        作者：{{ $post->author?->name ?? '—' }} · 类型 {{ $post->type }} · 可见性 {{ $post->visibility }}
                        · {{ $post->created_at->format('Y-m-d H:i') }}
                        · <a href="{{ route('admin.user-posts.show', $post) }}">查看详情</a>
                    </p>
                </header>
                <pre class="admin-post-body">{{ \Illuminate\Support\Str::limit($post->content, 1200) }}</pre>
                <div class="admin-post-actions">
                    <form method="post" action="{{ route('admin.user-posts.approve', $post) }}" style="display:inline;margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-primary">通过</button>
                    </form>
                    <form method="post" action="{{ route('admin.user-posts.reject', $post) }}" class="admin-reject-form">
                        @csrf
                        <label class="oc-label" for="audit-{{ $post->id }}">拒绝原因</label>
                        <textarea id="audit-{{ $post->id }}" name="audit_note" required minlength="2" maxlength="500" rows="2" placeholder="请填写原因"></textarea>
                        <button type="submit" class="btn btn-secondary">拒绝</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="admin-muted">暂无待审核投稿。</p>
        @endforelse
    </div>

    <div class="pagination-wrap">
        {{ $posts->withQueryString()->links() }}
    </div>
@endsection
