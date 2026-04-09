@extends('layouts.site')

@section('title', $sop->title . ' — SOP')

@section('content')
    <div class="max-w-3xl mx-auto">
        <p class="mb-4">
            <a href="{{ route('sops.index') }}" class="oc-link text-sm" style="text-decoration: none;">← SOP 列表</a>
        </p>
        <h1 class="text-2xl md:text-3xl font-bold oc-heading mb-2">{{ $sop->title }}</h1>
        <p class="text-sm oc-muted mb-6">
            平台 {{ $sop->platform }} · 类型 {{ $sop->type }}
            @if ($vipGate)
                <span class="ml-2 text-xs font-semibold px-2 py-0.5 rounded" style="background: rgba(245, 158, 11, 0.12); color: #b45309;">会员互动</span>
            @endif
        </p>

        @if ($sop->summary)
            <p class="text-sm oc-muted mb-6 leading-relaxed">{{ $sop->summary }}</p>
        @endif

        <div class="oc-surface p-6 md:p-8 article-content text-sm leading-relaxed mb-6" style="color: var(--dark);">
            {!! $bodyHtml !!}
        </div>

        @if (! empty($sop->checklist) && is_array($sop->checklist))
            <div class="oc-surface p-6 mb-6">
                <h2 class="text-lg font-bold oc-heading mb-3">检查清单</h2>
                <ul class="list-disc pl-5 text-sm oc-muted space-y-1 m-0">
                    @foreach ($sop->checklist as $item)
                        <li>{{ is_string($item) ? $item : json_encode($item, JSON_UNESCAPED_UNICODE) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! empty($sop->templates) && is_array($sop->templates))
            <div class="oc-surface p-6 mb-6">
                <h2 class="text-lg font-bold oc-heading mb-3">话术模板</h2>
                <ul class="list-disc pl-5 text-sm oc-muted space-y-1 m-0">
                    @foreach ($sop->templates as $item)
                        <li>{{ is_string($item) ? $item : json_encode($item, JSON_UNESCAPED_UNICODE) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! empty($sop->metrics) && is_array($sop->metrics))
            <div class="oc-surface p-6 mb-6">
                <h2 class="text-lg font-bold oc-heading mb-3">指标</h2>
                <ul class="list-disc pl-5 text-sm oc-muted space-y-1 m-0">
                    @foreach ($sop->metrics as $item)
                        <li>{{ is_string($item) ? $item : json_encode($item, JSON_UNESCAPED_UNICODE) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! empty($sop->tools) && is_array($sop->tools))
            <div class="oc-surface p-6 mb-6">
                <h2 class="text-lg font-bold oc-heading mb-3">工具</h2>
                <ul class="list-disc pl-5 text-sm oc-muted space-y-1 m-0">
                    @foreach ($sop->tools as $item)
                        <li>{{ is_string($item) ? $item : json_encode($item, JSON_UNESCAPED_UNICODE) }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($sop->contact_note)
            <div class="oc-surface p-6 mb-8">
                <h2 class="text-lg font-bold oc-heading mb-2">联系方式</h2>
                @if ($canSeeContact)
                    <div class="text-sm oc-muted whitespace-pre-wrap">{{ $sop->contact_note }}</div>
                @else
                    <p class="text-sm oc-muted m-0">已开启会员可见，请 <a href="{{ route('pricing') }}" class="oc-link font-semibold" style="text-decoration: none;">开通 VIP</a> 后查看。</p>
                @endif
            </div>
        @endif

        <section class="oc-surface p-6">
            <h2 class="text-xl font-bold mb-4 oc-heading">评论</h2>
            @if ($canComment)
                @auth
                    <form method="post" action="{{ route('sops.comments.store', $sop) }}" class="oc-comment-form-ajax mb-8">
                        @csrf
                        <input type="hidden" name="ajax" value="1" />
                        <label class="oc-label" for="sop-comment-content">发表评论</label>
                        <textarea name="content" id="sop-comment-content" rows="4" required minlength="1" class="oc-input mb-2" placeholder="输入评论内容"></textarea>
                        <button type="submit" class="btn btn-primary text-sm">发表评论</button>
                    </form>
                @else
                    <p class="text-sm mb-4 oc-muted">
                        <a href="{{ route('login', ['return' => request()->path()]) }}" class="oc-link font-semibold" style="text-decoration: none;">请先登录</a>
                        后发表评论
                    </p>
                @endauth
            @else
                <p class="text-sm mb-4 oc-muted m-0">本 SOP 已限制为 <strong>VIP</strong> 可评论，请 <a href="{{ route('pricing') }}" class="oc-link font-semibold" style="text-decoration: none;">开通会员</a>。</p>
            @endif

            <div id="comments-list">
                @forelse ($comments as $comment)
                    @include('partials.comment-thread', ['root' => $comment, 'likedIds' => $likedCommentIds ?? [], 'commentContext' => 'sop'])
                @empty
                    <p class="text-sm oc-muted oc-comments-empty m-0">暂无评论</p>
                @endforelse
            </div>
            <div class="mt-6">
                {{ $comments->onEachSide(1)->links() }}
            </div>
        </section>
    </div>
@endsection
