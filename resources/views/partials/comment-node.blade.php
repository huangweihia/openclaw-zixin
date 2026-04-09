@php
    $likedIds = $likedIds ?? [];
    $isReply = $isReply ?? false;
    $isProject = $isProject ?? false;
    $ctx = $commentContext ?? ($isProject ? 'project' : 'article');
    $isLiked = in_array($c->id, $likedIds, true);
    $replyRouteName = match ($ctx) {
        'project' => 'projects.comments.reply',
        'sop' => 'sops.comments.reply',
        'user_post' => 'posts.comments.reply',
        default => 'articles.comments.reply',
    };
    $likeRoute = match ($ctx) {
        'project' => route('projects.comments.like', $c),
        'sop' => route('sops.comments.like', $c),
        'user_post' => route('posts.comments.like', $c),
        default => route('articles.comments.like', $c),
    };
    $reportRoute = match ($ctx) {
        'project' => route('projects.comments.report', $c),
        'sop' => route('sops.comments.report', $c),
        'user_post' => route('posts.comments.report', $c),
        default => route('articles.comments.report', $c),
    };
@endphp
<div class="oc-comment {{ $isReply ? 'oc-comment--reply' : '' }}" id="comment-{{ $c->id }}">
    <div class="flex gap-3">
        <button
            type="button"
            class="oc-comment__avatar-btn shrink-0 p-0 border-0 bg-transparent cursor-pointer rounded-full focus:outline-none focus-visible:ring-2 ring-offset-2 ring-[var(--primary,#0d9488)]"
            data-oc-user-card="{{ $c->user_id }}"
            aria-label="查看 {{ $c->user->name ?? '用户' }} 的资料"
        >
            @if (! empty($c->user->avatar))
                <img src="{{ $c->user->avatar }}" alt="" class="w-9 h-9 rounded-full object-cover block" loading="lazy" />
            @else
                <span
                    class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-white text-xs block"
                    style="background: var(--gradient-primary);"
                >{{ mb_substr($c->user->name ?? '?', 0, 1) }}</span>
            @endif
        </button>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap gap-2 items-baseline mb-1">
                <span class="font-semibold text-sm oc-heading">{{ $c->user->name ?? '用户' }}</span>
                @if (($c->user->role ?? '') === 'vip' || ($c->user->role ?? '') === 'svip')
                    <span class="text-[10px] px-1.5 py-0.5 rounded oc-muted border border-slate-200">{{ ($c->user->role ?? '') === 'svip' ? 'SVIP' : 'VIP' }}</span>
                @endif
                <span class="text-xs oc-muted">{{ $c->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-sm whitespace-pre-wrap oc-heading">{{ $c->content }}</p>
            @auth
                <div class="oc-comment__actions">
                    <button
                        type="button"
                        class="oc-comment__btn oc-comment-like"
                        data-url="{{ $likeRoute }}"
                        data-liked="{{ $isLiked ? '1' : '0' }}"
                    >
                        <svg class="oc-comment__btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                        </svg>
                        <span class="oc-like-label">{{ $isLiked ? '已赞' : '点赞' }}</span>
                        <span class="oc-like-count">({{ (int) $c->like_count }})</span>
                    </button>
                    <button type="button" class="oc-comment__btn oc-comment-report oc-comment__btn--danger" data-url="{{ $reportRoute }}">
                        <svg class="oc-comment__btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        举报
                    </button>
                    <button
                        type="button"
                        class="oc-comment__btn"
                        onclick="document.getElementById('reply-wrap-{{ $c->id }}').classList.toggle('hidden')"
                    >
                        <svg class="oc-comment__btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                        回复
                    </button>
                </div>
                <div id="reply-wrap-{{ $c->id }}" class="hidden mt-2">
                    <form
                        class="oc-comment-form-ajax"
                        method="post"
                        action="{{ route($replyRouteName, $c) }}"
                        data-is-reply="1"
                    >
                        @csrf
                        <input type="hidden" name="ajax" value="1" />
                        <textarea name="content" class="oc-input" rows="2" required minlength="1" placeholder="输入回复"></textarea>
                        <button type="submit" class="btn btn-secondary text-sm mt-2">发表回复</button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</div>
