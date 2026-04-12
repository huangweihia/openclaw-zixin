@extends('layouts.site')

@section('title', '工具变现地图 — OpenClaw 智信')

@section('content')
    <div class="mb-8">
        <h1 class="text-3xl font-bold oc-heading mb-2">AI 工具变现地图</h1>
        <p class="text-sm oc-muted">场景、渠道与定价参考；标有 VIP 的工具需会员阅读全文。</p>
    </div>

    <div class="flex flex-wrap gap-2 mb-8">
        <a href="{{ route('tools.index') }}" class="oc-filter-pill {{ $currentCategory === '' ? 'oc-filter-pill--active' : '' }}">全部</a>
        @foreach (['text' => '文本', 'image' => '图像', 'video' => '视频', 'audio' => '音频', 'code' => '代码'] as $k => $lab)
            <a href="{{ route('tools.index', ['category' => $k]) }}" class="oc-filter-pill {{ $currentCategory === $k ? 'oc-filter-pill--active' : '' }}">{{ $lab }}</a>
        @endforeach
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($tools as $tool)
            @php
                $toolCardLocked = ! $tool->userCanReadFull(auth()->user());
            @endphp
            <div class="oc-surface p-5 rounded-xl oc-quick-tile relative overflow-hidden">
                @if ($toolCardLocked)
                    <div class="block" style="text-decoration: none; color: inherit;">
                @else
                    <a href="{{ route('tools.show', $tool) }}" class="block" style="text-decoration: none; color: inherit;">
                @endif
                    <div class="flex justify-between gap-2 mb-2">
                        <span class="text-xs oc-muted">{{ $tool->category }} · {{ $tool->pricing_model }}</span>
                        @if ($tool->visibility === 'vip')
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded" style="background: color-mix(in srgb, var(--primary) 20%, transparent); color: var(--primary);">VIP</span>
                        @endif
                    </div>
                    <h2 class="text-base font-bold oc-heading m-0">{{ $tool->tool_name }}</h2>
                    @if ($tool->tool_url)
                        <p class="text-xs oc-muted truncate mt-2 mb-0">{{ $tool->tool_url }}</p>
                    @endif
                @if ($toolCardLocked)
                    </div>
                    <x-vip-content-lock title="VIP 专属工具" desc="开通 VIP 后可查看完整变现说明与模板。" cta="开通 VIP 阅读全文" :href="route('pricing')" />
                @else
                    </a>
                @endif
            </div>
        @empty
            <div class="oc-surface p-8 text-center text-sm oc-muted sm:col-span-2 lg:col-span-3">暂无工具条目，请稍后再来或由管理员在后台添加。</div>
        @endforelse
    </div>

    @if ($tools->count() > 0)
        <div class="mt-10">{{ $tools->links() }}</div>
    @endif

    @if (isset($userToolPosts) && $userToolPosts->isNotEmpty())
        <div class="mt-14">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-xl font-bold oc-heading m-0">社区投稿 · 工具</h2>
                    <p class="text-sm oc-muted m-0 mt-1">用户投稿且审核通过，类型为「工具」</p>
                </div>
                <a href="{{ route('posts.index', ['type' => 'tool']) }}" class="oc-link text-sm font-medium" style="text-decoration: none;">投稿广场 →</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($userToolPosts as $post)
                    <a href="{{ route('posts.show', $post) }}" class="oc-surface p-5 block rounded-xl oc-quick-tile" style="text-decoration: none;">
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded oc-muted border border-slate-200">投稿</span>
                        <h3 class="text-base font-bold oc-heading line-clamp-2 mt-2 m-0">{{ $post->title }}</h3>
                        <p class="text-xs oc-muted mt-2 mb-0 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 100) }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@endsection
