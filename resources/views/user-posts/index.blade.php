@extends('layouts.site')

@section('title', '我的发布 — OpenClaw 智信')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold oc-heading m-0">我的发布</h1>
            </div>
            <a href="{{ route('user-posts.create') }}" class="btn btn-primary text-sm">新建投稿</a>
        </div>

        @if (session('success'))
            <div class="oc-flash oc-flash--success mb-4 text-sm" role="status">{{ session('success') }}</div>
        @endif

        @php
            $typeLabels = [
                'case' => '副业案例',
                'tool' => '工具推荐',
                'experience' => '经验心得',
                'resource' => '资源分享',
                'question' => '提问讨论',
            ];
            $statusLabels = [
                'pending' => '审核中',
                'approved' => '已通过',
                'rejected' => '未通过',
            ];
        @endphp

        <div class="space-y-3">
            @forelse ($posts as $post)
                <div class="oc-surface p-4 flex flex-wrap justify-between gap-3 items-start">
                    <div class="min-w-0">
                        <div class="flex flex-wrap gap-2 items-center mb-1">
                            <span class="text-xs px-2 py-0.5 rounded oc-muted" style="background: rgba(148,163,184,.15);">{{ $typeLabels[$post->type] ?? $post->type }}</span>
                            <span class="text-xs oc-muted">{{ $statusLabels[$post->status] ?? $post->status }}</span>
                            <span class="text-xs oc-muted">可见：{{ $post->visibility }}</span>
                        </div>
                        <h2 class="text-base font-semibold oc-heading m-0">{{ $post->title }}</h2>
                        <p class="text-xs oc-muted m-0 mt-1">{{ $post->updated_at->format('Y-m-d H:i') }}</p>
                    </div>
                    @if ($post->status === 'approved')
                        <a href="{{ route('posts.show', $post) }}" class="btn btn-secondary text-xs shrink-0" style="text-decoration: none;">查看公开页</a>
                    @endif
                </div>
            @empty
                <div class="oc-surface p-8 text-center text-sm oc-muted">暂无投稿，点击「新建投稿」开始分享。</div>
            @endforelse
        </div>

        <div class="mt-6">{{ $posts->links() }}</div>
    </div>
@endsection
