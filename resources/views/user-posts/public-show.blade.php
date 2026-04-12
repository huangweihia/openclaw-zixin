@extends('layouts.site')

@section('title', $post->title . ' — 投稿')

@section('content')
    <div class="max-w-3xl mx-auto">
        <p class="mb-4">
            <a href="{{ route('posts.index') }}" class="oc-link text-sm font-medium" style="text-decoration: none;">← 投稿广场</a>
        </p>
        <header class="mb-6">
            <div class="flex flex-wrap gap-2 items-center mb-2">
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full oc-muted border border-slate-200">{{ $typeLabel }}</span>
                @if ($post->visibility === 'vip')
                    <span class="text-xs font-semibold px-2 py-0.5 rounded" style="background: rgba(245, 158, 11, 0.15); color: #b45309;">VIP 全文</span>
                @endif
            </div>
            <h1 class="text-2xl md:text-3xl font-bold oc-heading mb-2">{{ $post->title }}</h1>
            <div class="text-sm oc-muted flex flex-wrap gap-3">
                <span>{{ $post->author?->name ?? '用户' }}</span>
                <span>{{ $post->audited_at?->format('Y-m-d H:i') ?? $post->updated_at->format('Y-m-d H:i') }}</span>
                <span>阅读 {{ number_format($post->view_count) }}</span>
            </div>
        </header>

        <div class="oc-surface p-6 md:p-8 article-content max-w-none text-sm leading-relaxed" style="color: var(--dark);">
            @if ($canReadFull)
                {!! $bodyHtml !!}
            @else
                @php
                    $gateMask = \App\Support\SiteGateMask::forVipExclusive(auth()->user(), request()->fullUrl());
                @endphp
                @include('partials.gated-content-teaser', [
                    'teaserHtml' => '<p class="oc-muted m-0">'.e($teaser).'</p>',
                    'mask' => $gateMask,
                ])
            @endif
        </div>

        @if (! $canReadFull)
            <section class="mt-12 oc-surface p-6">
                <h2 class="text-xl font-bold mb-4 oc-heading">评论</h2>
                <p class="text-sm oc-muted m-0">开通 VIP 后可阅读全文并参与评论与互动。</p>
            </section>
        @else
            <div class="flex flex-wrap gap-3 mt-8">
                @auth
                    <form method="post" action="{{ route('posts.like', $post) }}" class="oc-engage-ajax">
                        @csrf
                        <button type="submit" class="btn {{ $userLiked ? 'btn-primary' : 'btn-secondary' }}" data-on-text="❤️ 已赞" data-off-text="🤍 点赞">
                            {{ $userLiked ? '❤️ 已赞' : '🤍 点赞' }} · {{ number_format($post->like_count) }}
                        </button>
                    </form>
                    <form method="post" action="{{ route('posts.favorite', $post) }}" class="oc-engage-ajax">
                        @csrf
                        <button type="submit" class="btn {{ $userFavorited ? 'btn-primary' : 'btn-secondary' }}" data-on-text="⭐ 已收藏" data-off-text="☆ 收藏">
                            {{ $userFavorited ? '⭐ 已收藏' : '☆ 收藏' }}
                        </button>
                    </form>
                    @if ((int) ($boostCost ?? 0) > 0)
                        <form method="post" action="{{ route('posts.boost', $post) }}" class="m-0" onsubmit="return confirm('确认消耗 {{ (int) $boostCost }} 积分加热本篇投稿？\n加热后将通过消息通知触达作者与部分用户，前台不展示加热人名单。');">
                            @csrf
                            <button type="submit" class="btn btn-secondary text-sm">🔥 加热（{{ (int) $boostCost }} 积分）</button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('login', ['return' => request()->path()]) }}" class="btn btn-secondary">登录后点赞 / 收藏</a>
                @endauth
            </div>

            <section class="mt-12 oc-surface p-6">
                <h2 class="text-xl font-bold mb-4 oc-heading">评论</h2>
                @auth
                    <form method="post" action="{{ route('posts.comments.store', $post) }}" class="oc-comment-form-ajax mb-8">
                        @csrf
                        <input type="hidden" name="ajax" value="1" />
                        <label class="oc-label" for="post-comment-content">发表评论</label>
                        <textarea name="content" id="post-comment-content" rows="4" required minlength="1" class="oc-input mb-2" placeholder="输入评论内容"></textarea>
                        <button type="submit" class="btn btn-primary text-sm">发表评论</button>
                    </form>
                @else
                    <p class="text-sm mb-4 oc-muted">
                        <a href="{{ route('login', ['return' => request()->path()]) }}" class="oc-link font-semibold" style="text-decoration: none;">请先登录</a>
                        后发表评论
                    </p>
                @endauth
                <div id="comments-list">
                    @forelse ($comments as $comment)
                        @include('partials.comment-thread', ['root' => $comment, 'likedIds' => $likedCommentIds ?? [], 'commentContext' => 'user_post'])
                    @empty
                        <p class="text-sm oc-muted oc-comments-empty m-0">暂无评论</p>
                    @endforelse
                </div>
                <div class="mt-6">
                    {{ $comments->onEachSide(1)->links() }}
                </div>
            </section>
        @endif

        <x-guess-you-like :exclude-user-post-id="$post->id" />
    </div>
    @include('partials.engagement-scripts')
@endsection
