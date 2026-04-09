@extends('layouts.site')

@section('title', $article->title . ' — OpenClaw 智信')

@section('content')
    <div id="read-progress-track" class="oc-read-track" aria-hidden="true">
        <div id="read-progress-bar" class="oc-read-bar"></div>
    </div>

    <div class="lg:grid lg:grid-cols-12 lg:gap-10">
        <article class="lg:col-span-8">
            <header class="mb-6">
                @if ($article->category)
                    <a href="{{ route('articles.index', ['category' => $article->category->slug]) }}"
                        class="text-sm font-medium mb-2 inline-block"
                        style="color: var(--primary);">{{ $article->category->name }}</a>
                @endif
                <h1 class="text-3xl md:text-4xl font-bold mb-4" style="color: var(--dark);">{{ $article->title }}</h1>
                <div class="flex flex-wrap gap-4 text-sm" style="color: var(--gray);">
                    @if ($article->author)
                        <span style="color: var(--dark);">{{ $article->author->name }}</span>
                    @endif
                    <span>{{ $article->published_at?->format('Y-m-d H:i') }}</span>
                    <span>阅读 {{ number_format($article->view_count) }}</span>
                    @if ($article->is_vip)
                        <span class="font-semibold" style="color: #b45309;">VIP 专属</span>
                    @endif
                </div>
            </header>

            @include('partials.ad-slot', ['code' => 'article-top'])

            @if ($article->cover_image)
                <button type="button" id="cover-open" class="w-full rounded-xl overflow-hidden mb-8 border-0 p-0 cursor-zoom-in bg-transparent">
                    <img src="{{ $article->cover_image }}" alt="" class="w-full max-h-[28rem] object-cover" />
                </button>
            @endif

            <div class="oc-surface p-6 md:p-8 article-content max-w-none" id="article-body" style="color: var(--dark);">
                @if ($canReadFull)
                    {!! $article->content !!}
                @else
                    <div class="mb-6">{!! $article->summary !!}</div>
                    <div class="oc-vip-lock">
                        <div class="text-3xl mb-2">🔒</div>
                        <p class="font-semibold mb-2" style="color: var(--dark);">本文为 VIP 专属内容</p>
                        <p class="text-sm mb-4" style="color: var(--gray);">开通 VIP 后可阅读全文</p>
                        @guest
                            <a href="{{ route('login', ['return' => request()->path()]) }}" class="btn btn-primary">请先登录</a>
                        @else
                            <a href="{{ route('pricing') }}" class="btn btn-primary">解锁 VIP</a>
                        @endguest
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap gap-3 mt-8">
                @auth
                    <form method="post" action="{{ route('articles.like', $article) }}">
                        @csrf
                        <button type="submit" class="btn {{ $userLiked ? 'btn-primary' : 'btn-secondary' }}">
                            {{ $userLiked ? '❤️ 已赞' : '🤍 点赞' }} · {{ number_format($article->like_count) }}
                        </button>
                    </form>
                    <form method="post" action="{{ route('articles.favorite', $article) }}">
                        @csrf
                        <button type="submit" class="btn {{ $userFavorited ? 'btn-primary' : 'btn-secondary' }}">
                            {{ $userFavorited ? '⭐ 已收藏' : '☆ 收藏' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login', ['return' => request()->path()]) }}" class="btn btn-secondary">登录后点赞 / 收藏</a>
                @endauth
            </div>

            <section class="mt-12 oc-surface p-6">
                <h2 class="text-xl font-bold mb-4" style="color: var(--dark);">评论</h2>

                @auth
                    <form
                        method="post"
                        action="{{ route('articles.comments.store', $article) }}"
                        class="oc-comment-form-ajax mb-8"
                    >
                        @csrf
                        <input type="hidden" name="ajax" value="1" />
                        <label class="oc-label" for="comment-content">发表评论</label>
                        <textarea name="content" id="comment-content" rows="4" required minlength="1" class="oc-input mb-2" placeholder="输入评论内容">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="auth-err mb-2">{{ $message }}</p>
                        @enderror
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
                        @include('partials.comment-thread', ['root' => $comment, 'likedIds' => $likedCommentIds ?? [], 'isProject' => false])
                    @empty
                        <p class="text-sm oc-muted oc-comments-empty">暂无评论，来抢沙发吧</p>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $comments->onEachSide(1)->links() }}
                </div>
            </section>
        </article>

        <aside class="lg:col-span-4 mt-10 lg:mt-0">
            @include('partials.ad-slot', ['code' => 'article-sidebar'])
            <div class="oc-surface p-6 sticky top-24">
                <h3 class="text-lg font-bold mb-4" style="color: var(--dark);">相关文章</h3>
                <ul class="space-y-3">
                    @foreach ($related as $rel)
                        <li>
                            <a href="{{ route('articles.show', $rel) }}" class="block rounded-lg p-2 -mx-2 transition hover:bg-slate-50">
                                <span class="text-sm font-medium line-clamp-2" style="color: var(--dark);">{{ $rel->title }}</span>
                                <span class="text-xs mt-1 block" style="color: var(--gray-light);">👁 {{ number_format($rel->view_count) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>
    </div>

    @include('partials.comment-report-modal')
    @include('partials.comment-scripts')

    @if ($article->cover_image)
        <div id="cover-modal" class="oc-modal-overlay hidden" role="dialog" aria-modal="true">
            <div class="max-w-5xl w-full p-2">
                <button type="button" id="cover-close" class="block ml-auto mb-2 text-white text-sm underline">关闭</button>
                <img src="{{ $article->cover_image }}" alt="" class="w-full rounded-lg max-h-[85vh] object-contain bg-black/40" />
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    @if ($article->cover_image)
        <script>
            (function () {
                const modal = document.getElementById('cover-modal');
                document.getElementById('cover-open')?.addEventListener('click', function () {
                    modal.classList.remove('hidden');
                });
                document.getElementById('cover-close')?.addEventListener('click', function () {
                    modal.classList.add('hidden');
                });
                modal?.addEventListener('click', function (e) {
                    if (e.target === modal) modal.classList.add('hidden');
                });
            })();
        </script>
    @endif
    <script>
        (function () {
            const bar = document.getElementById('read-progress-bar');
            const body = document.getElementById('article-body');
            if (!bar || !body) return;
            function onScroll() {
                const rect = body.getBoundingClientRect();
                const total = body.offsetHeight - window.innerHeight * 0.5;
                const scrolled = Math.min(Math.max(-rect.top, 0), total);
                const pct = total > 0 ? (scrolled / total) * 100 : 0;
                bar.style.width = Math.min(100, Math.max(0, pct)) + '%';
            }
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        })();
    </script>
@endpush
