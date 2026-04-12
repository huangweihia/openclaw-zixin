@extends('layouts.site')

@section('title', '我的评论 — OpenClaw 智信')

@section('content')
    <h1 class="text-2xl font-bold mb-6 oc-heading">我的评论</h1>

    <div class="oc-surface p-6">
        @forelse ($items as $c)
            @php
                $target = $c->commentable;
                $title = '内容';
                $url = '#';
                if ($target instanceof \App\Models\Article) {
                    $title = $target->title;
                    $url = route('articles.show', $target);
                } elseif ($target instanceof \App\Models\Project) {
                    $title = $target->name;
                    $url = route('projects.show', $target);
                } elseif ($target instanceof \App\Models\UserPost) {
                    $title = $target->title;
                    $url = route('posts.show', $target);
                }
            @endphp
            <div class="py-4 border-b oc-border last:border-0">
                <p class="text-xs oc-muted mb-1">{{ $c->created_at->format('Y-m-d H:i') }}</p>
                <p class="text-sm oc-heading mb-2 line-clamp-2">{{ $c->content }}</p>
                @if ($target)
                    <a href="{{ $url }}" class="oc-link text-sm" style="text-decoration: none;">查看《{{ \Illuminate\Support\Str::limit($title, 40) }}》</a>
                @else
                    <span class="text-xs oc-muted">原内容已删除</span>
                @endif
            </div>
        @empty
            <p class="text-sm oc-muted m-0">暂无评论记录。</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $items->links() }}</div>
@endsection
