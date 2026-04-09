@extends('layouts.site')

@section('title', '投稿广场 — OpenClaw 智信')

@section('content')
    <div class="mb-4">
        <h1 class="text-3xl font-bold oc-heading mb-2">投稿广场</h1>
        <p class="text-sm oc-muted">审核通过的公开投稿；类型为「案例」「工具」的条目会同步出现在案例 / 工具页的「社区投稿」区域。</p>
    </div>

    <div class="oc-surface p-4 md:p-6 mb-5">
        <div class="flex flex-wrap gap-2 items-center">
            <span class="text-sm font-medium w-full md:w-auto oc-muted">类型</span>
            <a href="{{ route('posts.index') }}" class="oc-filter-pill {{ $currentType === null ? 'oc-filter-pill--active' : '' }}">全部</a>
            @foreach ($typeLabels as $key => $lab)
                <a href="{{ route('posts.index', ['type' => $key]) }}"
                    class="oc-filter-pill {{ $currentType === $key ? 'oc-filter-pill--active' : '' }}">{{ $lab }}</a>
            @endforeach
        </div>
    </div>

    @if ($posts->isEmpty())
        <div class="oc-surface p-12 text-center oc-muted">暂无投稿</div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($posts as $post)
                <a href="{{ route('posts.show', $post) }}" class="oc-article-card">
                    <div class="oc-card-thumb flex items-center justify-center text-2xl" style="background: var(--light);">
                        📝
                    </div>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <h2 class="text-lg font-bold line-clamp-2 oc-heading m-0">{{ $post->title }}</h2>
                            <span class="text-xs shrink-0 px-2 py-0.5 rounded-full font-semibold oc-muted border border-slate-200">{{ $typeLabels[$post->type] ?? $post->type }}</span>
                        </div>
                        <p class="text-sm line-clamp-2 oc-muted mb-4 m-0">{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 120) }}</p>
                        <div class="flex justify-between text-xs oc-muted">
                            <span>{{ $post->author?->name ?? '用户' }}</span>
                            <span>👁 {{ number_format($post->view_count) }} · ❤️ {{ number_format($post->like_count) }} · 💬 {{ number_format($post->comment_count) }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-10 flex justify-center">
            {{ $posts->onEachSide(1)->links() }}
        </div>
    @endif
@endsection
