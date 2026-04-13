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
            <div class="text-sm oc-muted flex flex-wrap gap-3 items-center">
                @if ($post->author)
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 border-0 bg-transparent cursor-pointer p-0 oc-link font-medium"
                        style="text-decoration: none;"
                        data-oc-user-card="{{ $post->user_id }}"
                        aria-label="查看 {{ $post->author->name }} 的资料"
                    >
                        @if (! empty($post->author->avatar))
                            <img src="{{ $post->author->avatar }}" alt="" class="w-8 h-8 rounded-full object-cover" loading="lazy" />
                        @else
                            <span
                                class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white"
                                style="background: var(--gradient-primary);"
                            >{{ mb_substr($post->author->name, 0, 1) }}</span>
                        @endif
                        <span>{{ $post->author->name }}</span>
                    </button>
                @else
                    <span>用户</span>
                @endif
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
                        <button
                            type="button"
                            id="oc-post-boost-open"
                            class="btn btn-secondary text-sm"
                            data-boost-url="{{ route('posts.boost', $post) }}"
                        >🔥 加热（{{ (int) $boostCost }} 积分）</button>
                        <x-confirm-modal id="oc-post-boost-modal" title="确认加热" confirm-label="确认加热" cancel-label="取消">
                            <p class="m-0 mb-2">
                                确认消耗 <strong class="oc-heading">{{ (int) $boostCost }}</strong> 积分加热本篇投稿？
                            </p>
                            <p class="m-0 text-xs">
                                加热后将通过消息通知触达作者与部分用户；前台不展示加热人名单。
                            </p>
                        </x-confirm-modal>
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
    @if ((int) ($boostCost ?? 0) > 0 && auth()->check() && ($canReadFull ?? false))
        @push('scripts')
            <script>
                (function () {
                    const modal = document.getElementById('oc-post-boost-modal');
                    const openBtn = document.getElementById('oc-post-boost-open');
                    if (!modal || !openBtn) return;
                    const ok = modal.querySelector('[data-oc-confirm-ok]');
                    const cancel = modal.querySelector('[data-oc-confirm-cancel]');
                    const url = openBtn.getAttribute('data-boost-url');
                    const csrf = function () {
                        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    };
                    const toast = function (msg, type) {
                        if (typeof window.ocToast === 'function') window.ocToast(msg, type || 'success');
                    };
                    function open() {
                        modal.classList.remove('hidden');
                    }
                    function close() {
                        modal.classList.add('hidden');
                    }
                    openBtn.addEventListener('click', open);
                    cancel.addEventListener('click', close);
                    modal.addEventListener('click', function (e) {
                        if (e.target === modal) close();
                    });
                    document.addEventListener('keydown', function (e) {
                        if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
                    });
                    ok.addEventListener('click', async function () {
                        close();
                        openBtn.disabled = true;
                        try {
                            var fd = new FormData();
                            fd.append('_token', csrf());
                            var res = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    Accept: 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': csrf(),
                                },
                                credentials: 'same-origin',
                                body: fd,
                            });
                            var data = await res.json().catch(function () {
                                return {};
                            });
                            if (res.status === 419) {
                                toast('页面已过期，请刷新后重试', 'error');
                                return;
                            }
                            if (!res.ok || !data.ok) {
                                toast(data.message || data.error || '加热失败', 'error');
                                return;
                            }
                            toast(data.message || '加热成功', 'success');
                            window.setTimeout(function () {
                                window.location.reload();
                            }, 400);
                        } catch (e) {
                            toast('网络错误，请稍后重试', 'error');
                        } finally {
                            openBtn.disabled = false;
                        }
                    });
                })();
            </script>
        @endpush
    @endif
@endsection
