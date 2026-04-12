@extends('layouts.site')

@section('suppress_floating_promos')
@endsection

@section('title', $project->name . ' — OpenClaw 智信')

@section('content')
    <div class="lg:grid lg:grid-cols-12 lg:gap-10">
        <div class="lg:col-span-8">
            <header class="mb-6">
                @if ($project->category)
                    <a href="{{ route('projects.index', ['category' => $project->category->slug]) }}" class="text-sm font-medium mb-2 inline-block oc-link" style="text-decoration: none;">{{ $project->category->name }}</a>
                @endif
                <h1 class="text-3xl md:text-4xl font-bold mb-4 oc-heading">{{ $project->name }}</h1>
                @if ($project->full_name)
                    <p class="text-sm oc-muted mb-2">{{ $project->full_name }}</p>
                @endif
                <div class="flex flex-wrap gap-3 text-sm oc-muted">
                    @if ($project->url)
                        <a href="{{ $project->url }}" target="_blank" rel="noopener noreferrer" class="oc-link font-semibold" style="text-decoration: none;">GitHub ↗</a>
                    @endif
                    <span>⭐ {{ number_format($project->stars) }}</span>
                    <span>⑂ {{ number_format($project->forks) }}</span>
                    @if ($project->language)
                        <span>{{ $project->language }}</span>
                    @endif
                </div>
            </header>

            @include('partials.ad-slot', ['code' => 'project-top'])

            <div class="oc-surface p-6 md:p-8 mb-8">
                <h2 class="text-lg font-bold mb-3 oc-heading">项目介绍</h2>
                <div class="text-sm leading-relaxed oc-heading whitespace-pre-wrap">{{ $project->description ?: '暂无描述' }}</div>
            </div>

            @if ($project->monetization || $project->difficulty)
                <div class="oc-surface p-6 mb-8">
                    <h2 class="text-lg font-bold mb-3 oc-heading">变现与难度</h2>
                    @if ($project->monetization)
                        <p class="text-sm oc-heading mb-3 whitespace-pre-wrap">{{ $project->monetization }}</p>
                    @endif
                    @php
                        $diffLabel = match ($project->difficulty) {
                            'easy' => '简单',
                            'hard' => '困难',
                            default => '中等',
                        };
                    @endphp
                    <p class="text-sm oc-muted">难度：<span class="font-semibold oc-heading">{{ $diffLabel }}</span></p>
                </div>
            @endif

            @php
                $projectTags = $project->tags;
                if (is_string($projectTags)) {
                    $decodedTags = json_decode($projectTags, true);
                    $projectTags = is_array($decodedTags) ? $decodedTags : [];
                }
                if (! is_array($projectTags)) {
                    $projectTags = [];
                }
            @endphp
            @if (! empty($projectTags))
                <div class="oc-surface p-6 mb-8">
                    <h2 class="text-lg font-bold mb-3 oc-heading">技术栈 / 标签</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($projectTags as $tag)
                            <a href="{{ route('projects.index', ['q' => $tag]) }}" class="oc-filter-pill text-xs">{{ $tag }}</a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex flex-wrap gap-3 mb-10">
                @auth
                    <form method="post" action="{{ route('projects.favorite', $project) }}" class="oc-engage-ajax">
                        @csrf
                        <button type="submit" class="btn {{ $userFavorited ? 'btn-primary' : 'btn-secondary' }}" data-on-text="⭐ 已收藏" data-off-text="☆ 收藏项目">
                            {{ $userFavorited ? '⭐ 已收藏' : '☆ 收藏项目' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login', ['return' => request()->path()]) }}" class="btn btn-secondary">登录后收藏</a>
                @endauth
            </div>

            <section class="oc-surface p-6">
                <h2 class="text-xl font-bold mb-4 oc-heading">评论</h2>
                @auth
                    <form method="post" action="{{ route('projects.comments.store', $project) }}" class="oc-comment-form-ajax mb-8">
                        @csrf
                        <input type="hidden" name="ajax" value="1" />
                        <label class="oc-label" for="p-comment">发表评论</label>
                        <textarea name="content" id="p-comment" rows="4" required minlength="1" class="oc-input mb-2" placeholder="输入评论内容">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="auth-err mb-2">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="btn btn-primary text-sm">发表评论</button>
                    </form>
                @else
                    <p class="text-sm mb-4 oc-muted">
                        <a href="{{ route('login', ['return' => request()->path()]) }}" class="oc-link font-semibold" style="text-decoration: none;">请先登录</a> 后发表评论
                    </p>
                @endauth

                <div id="comments-list">
                    @forelse ($comments as $comment)
                        @include('partials.comment-thread', ['root' => $comment, 'likedIds' => $likedCommentIds ?? [], 'isProject' => true])
                    @empty
                        <p class="text-sm oc-muted oc-comments-empty">暂无评论</p>
                    @endforelse
                </div>
                <div class="mt-6">{{ $comments->onEachSide(1)->links() }}</div>
            </section>
        </div>

        <aside class="lg:col-span-4 mt-10 lg:mt-0">
            @include('partials.ad-slot', ['code' => 'project-sidebar'])
            <div class="oc-surface p-6 sticky top-24">
                <h3 class="text-lg font-bold mb-4 oc-heading">相关项目</h3>
                <ul class="space-y-3">
                    @foreach ($related as $rel)
                        <li>
                            <a href="{{ route('projects.show', $rel) }}" class="block rounded-lg p-2 -mx-2 oc-related-row">
                                <span class="text-sm font-medium line-clamp-2 oc-heading">{{ $rel->name }}</span>
                                <span class="text-xs mt-1 block oc-muted">⭐ {{ number_format($rel->stars) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </div>

    <x-guess-you-like />

    @include('partials.comment-report-modal')
    @include('partials.comment-scripts')
    @include('partials.engagement-scripts')
@endsection
