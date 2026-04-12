@extends('layouts.site')

@section('title', '副业案例 — OpenClaw 智信')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold oc-heading mb-2">副业案例</h1>
        <p class="text-sm oc-muted">真实路径、成本与周期参考；VIP 专享案例带「VIP」角标。</p>
    </div>

    <div class="flex flex-wrap gap-2 mb-8">
        <a href="{{ route('cases.index') }}" class="oc-filter-pill {{ $currentCategory === '' ? 'oc-filter-pill--active' : '' }}">全部</a>
        @foreach (['online' => '线上', 'offline' => '线下', 'hybrid' => '混合'] as $k => $lab)
            <a href="{{ route('cases.index', ['category' => $k]) }}" class="oc-filter-pill {{ $currentCategory === $k ? 'oc-filter-pill--active' : '' }}">{{ $lab }}</a>
        @endforeach
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($cases as $case)
            <div class="oc-surface p-5 rounded-xl oc-quick-tile transition-shadow flex flex-col h-full">
                <a href="{{ route('cases.show', $case) }}" class="block flex-1 min-w-0" style="text-decoration: none;">
                    <div class="flex justify-between gap-2 mb-2">
                        <span class="text-xs oc-muted">{{ $case->category }} · {{ $case->type }}</span>
                        @if ($case->visibility === 'vip')
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded" style="background: color-mix(in srgb, var(--primary) 20%, transparent); color: var(--primary);">VIP</span>
                        @endif
                    </div>
                    <h2 class="text-base font-bold oc-heading line-clamp-2 m-0">{{ $case->title }}</h2>
                    @if ($case->summary)
                        <p class="text-sm oc-muted line-clamp-2 mt-2 mb-0">{{ $case->summary }}</p>
                    @endif
                    <p class="text-xs oc-muted mt-3 mb-0">预估月入 ¥{{ number_format((float) $case->estimated_income, 0) }} · {{ $case->time_investment }}</p>
                </a>
                @auth
                    <div class="flex flex-wrap gap-2 mt-4 pt-3 border-t border-slate-200 relative z-10">
                        <form method="post" action="{{ route('cases.like', $case) }}" class="oc-engage-ajax m-0">
                            @csrf
                            <button type="submit" class="btn text-xs px-3 py-1.5 {{ ! empty($caseLikedIds[$case->id] ?? null) ? 'btn-primary' : 'btn-secondary' }}" data-on-text="❤️ 已赞" data-off-text="🤍 点赞">
                                {{ ! empty($caseLikedIds[$case->id] ?? null) ? '❤️ 已赞' : '🤍 点赞' }} · {{ number_format((int) $case->like_count) }}
                            </button>
                        </form>
                        <form method="post" action="{{ route('cases.favorite', $case) }}" class="oc-engage-ajax m-0">
                            @csrf
                            <button type="submit" class="btn text-xs px-3 py-1.5 {{ ! empty($caseFavoritedIds[$case->id] ?? null) ? 'btn-primary' : 'btn-secondary' }}" data-on-text="⭐ 已收藏" data-off-text="☆ 收藏">
                                {{ ! empty($caseFavoritedIds[$case->id] ?? null) ? '⭐ 已收藏' : '☆ 收藏' }}
                            </button>
                        </form>
                    </div>
                @endauth
            </div>
        @empty
            <div class="oc-surface p-8 text-center text-sm oc-muted sm:col-span-2 lg:col-span-3">暂无已发布的案例，请稍后再来或由管理员在后台添加。</div>
        @endforelse
    </div>

    @if ($cases->count() > 0)
        <div class="mt-10">{{ $cases->links() }}</div>
    @endif

    @if (isset($userCasePosts) && $userCasePosts->isNotEmpty())
        <div class="mt-14">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-xl font-bold oc-heading m-0">社区投稿 · 案例</h2>
                    <p class="text-sm oc-muted m-0 mt-1">用户投稿且审核通过，类型为「案例」</p>
                </div>
                <a href="{{ route('posts.index', ['type' => 'case']) }}" class="oc-link text-sm font-medium" style="text-decoration: none;">投稿广场 →</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($userCasePosts as $post)
                    <a href="{{ route('posts.show', $post) }}" class="oc-surface p-5 block rounded-xl oc-quick-tile transition-shadow" style="text-decoration: none;">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded oc-muted border border-slate-200">投稿</span>
                        <h3 class="text-base font-bold oc-heading line-clamp-2 mt-2 m-0">{{ $post->title }}</h3>
                        <p class="text-xs oc-muted mt-2 mb-0 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 100) }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@endsection
