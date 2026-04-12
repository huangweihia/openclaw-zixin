@extends('layouts.site')

@section('title', $case->title . ' — 案例')

@section('content')
    <article class="max-w-7xl mx-auto">
        <p class="mb-4">
            <a href="{{ route('cases.index') }}" class="oc-link text-sm" style="text-decoration: none;">← 案例列表</a>
        </p>
        <div class="grid lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8">
                <header class="mb-8">
                    <h1 class="text-2xl md:text-3xl font-bold oc-heading mb-2">{{ $case->title }}</h1>
                    <p class="text-sm oc-muted m-0">
                        {{ $case->category }} · {{ $case->type }} · 启动成本 {{ $case->startup_cost }} · {{ $case->time_investment }}
                    </p>
                </header>

                <div class="flex flex-wrap gap-3 mb-6">
                    @auth
                        <form method="post" action="{{ route('cases.like', $case) }}" class="oc-engage-ajax">
                            @csrf
                            <button type="submit" class="btn {{ ($userLiked ?? false) ? 'btn-primary' : 'btn-secondary' }}" data-on-text="❤️ 已赞" data-off-text="🤍 点赞">
                                {{ ($userLiked ?? false) ? '❤️ 已赞' : '🤍 点赞' }} · {{ number_format((int) $case->like_count) }}
                            </button>
                        </form>
                        <form method="post" action="{{ route('cases.favorite', $case) }}" class="oc-engage-ajax">
                            @csrf
                            <button type="submit" class="btn {{ ($userFavorited ?? false) ? 'btn-primary' : 'btn-secondary' }}" data-on-text="⭐ 已收藏" data-off-text="☆ 收藏">
                                {{ ($userFavorited ?? false) ? '⭐ 已收藏' : '☆ 收藏' }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login', ['return' => request()->path()]) }}" class="btn btn-secondary text-sm">登录后点赞 / 收藏</a>
                    @endauth
                </div>

                <div class="oc-surface p-6 md:p-8 space-y-6">
                    @if ($canReadFull ?? true)
                        <section class="article-content text-sm leading-relaxed" style="color: var(--dark);">
                            {!! $bodyHtml !!}
                        </section>
                        @if ($stepsHtml)
                            <section>
                                <h2 class="text-lg font-bold oc-heading mb-3">操作步骤</h2>
                                <div class="article-content text-sm leading-relaxed" style="color: var(--dark);">
                                    {!! $stepsHtml !!}
                                </div>
                            </section>
                        @endif
                    @else
                        @include('partials.gated-content-teaser', [
                            'teaserHtml' => $teaserHtml ?? '',
                            'mask' => $gateMask ?? [],
                        ])
                    @endif
                </div>
            </div>
            <aside class="lg:col-span-4 space-y-4">
                <div class="oc-surface p-4">
                    <h2 class="font-bold oc-heading mb-3">相关推荐</h2>
                    @forelse(($recommendCases ?? collect()) as $rc)
                        <a href="{{ route('cases.show', $rc) }}" class="block py-2 border-b oc-border last:border-0" style="text-decoration:none;">
                            <p class="text-sm font-semibold oc-heading m-0 line-clamp-2">{{ $rc->title }}</p>
                            <p class="text-xs oc-muted mt-1 mb-0">👍 {{ number_format((int) $rc->like_count) }} · 👁 {{ number_format((int) $rc->view_count) }}</p>
                        </a>
                    @empty
                        <p class="text-sm oc-muted m-0">暂无推荐</p>
                    @endforelse
                </div>
            </aside>
        </div>

        <section class="mt-8 oc-surface p-6">
            <h2 class="text-xl font-bold mb-4 oc-heading">评论</h2>
            @if (! ($canReadFull ?? true))
                <p class="text-sm oc-muted m-0">开通 VIP 后可查看全文并参与评论。</p>
            @else
                @auth
                    <form method="post" action="{{ route('cases.comments.store', $case) }}" class="oc-comment-form-ajax mb-8">
                        @csrf
                        <input type="hidden" name="ajax" value="1" />
                        <label class="oc-label" for="case-comment-content">发表评论</label>
                        <textarea name="content" id="case-comment-content" rows="4" required minlength="1" class="oc-input mb-2" placeholder="输入评论内容"></textarea>
                        <button type="submit" class="btn btn-primary text-sm">发表评论</button>
                    </form>
                @else
                    <p class="text-sm mb-4 oc-muted">
                        <a href="{{ route('login', ['return' => request()->path()]) }}" class="oc-link font-semibold" style="text-decoration: none;">请先登录</a> 后发表评论
                    </p>
                @endauth
            @endif
            @if ($canReadFull ?? true)
                <div id="comments-list">
                    @forelse ($comments as $comment)
                        @include('partials.comment-thread', ['root' => $comment, 'likedIds' => $likedCommentIds ?? [], 'commentContext' => 'case'])
                    @empty
                        <p class="text-sm oc-muted oc-comments-empty m-0">暂无评论</p>
                    @endforelse
                </div>
                <div class="mt-6">{{ $comments->onEachSide(1)->links() }}</div>
            @endif
        </section>
    </article>

    <x-guess-you-like />

    @include('partials.comment-report-modal')
    @include('partials.comment-scripts')
@endsection
