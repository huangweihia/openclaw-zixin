@extends('layouts.site')

@section('title', '文章 — OpenClaw 智信')

@section('content')
    <div class="mb-4">
        <h1 class="text-3xl font-bold mb-2" style="color: var(--dark);">文章</h1>
        <p class="text-sm" style="color: var(--gray);">筛选、排序与搜索</p>
    </div>

    <div class="oc-surface p-4 md:p-6 mb-5">
        <form method="get" action="{{ route('articles.index') }}" id="article-filter-form" class="space-y-4">
            <div class="flex flex-wrap gap-2 items-center">
                <span class="text-sm font-medium w-full md:w-auto" style="color: var(--gray);">分类</span>
                <a href="{{ route('articles.index', array_filter(['sort' => $currentSort, 'q' => $searchQ ?: null])) }}"
                    class="oc-filter-pill {{ $currentCategory === '' ? 'oc-filter-pill--active' : '' }}">全部</a>
                @foreach ($categories as $cat)
                    <a href="{{ route('articles.index', array_filter(['category' => $cat->slug, 'sort' => $currentSort, 'q' => $searchQ ?: null])) }}"
                        class="oc-filter-pill {{ $currentCategory === $cat->slug ? 'oc-filter-pill--active' : '' }}">{{ $cat->name }}</a>
                @endforeach
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                <span class="text-sm font-medium w-full md:w-auto" style="color: var(--gray);">排序</span>
                @php
                    $sortLinks = [
                        'latest' => '最新',
                        'hot' => '最热',
                        'vip' => 'VIP 专属',
                    ];
                @endphp
                @foreach ($sortLinks as $key => $label)
                    <a href="{{ route('articles.index', array_filter(['category' => $currentCategory ?: null, 'sort' => $key, 'q' => $searchQ ?: null])) }}"
                        class="oc-filter-pill {{ $currentSort === $key ? 'oc-filter-pill--active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                <label for="q" class="text-sm font-medium shrink-0" style="color: var(--gray);">搜索</label>
                <input type="search" name="q" id="q" value="{{ $searchQ }}"
                    placeholder="标题 / 摘要 / 正文"
                    class="oc-input flex-1 max-w-xl"
                    autocomplete="off" />
                @if ($currentCategory !== '')
                    <input type="hidden" name="category" value="{{ $currentCategory }}" />
                @endif
                <input type="hidden" name="sort" value="{{ $currentSort }}" />
                <button type="submit" class="btn btn-secondary text-sm">搜索</button>
            </div>
        </form>
    </div>

    @if ($articles->isEmpty())
        <div class="oc-surface p-12 text-center" style="color: var(--gray);">暂无文章，请调整筛选条件</div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($articles as $article)
                @php
                    $vipLocked = $article->is_vip && !($canAccessVip ?? false);
                @endphp
                <div class="oc-article-card relative">
                    @if ($vipLocked)
                        <div class="block text-inherit no-underline">
                    @else
                        <a href="{{ route('articles.show', $article) }}" class="block text-inherit no-underline">
                    @endif
                        @include('partials.article-cover-thumb', ['cover' => $article->cover_image])
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h2 class="text-lg font-bold line-clamp-2" style="color: var(--dark);">{{ $article->title }}</h2>
                                @if ($article->is_vip)
                                    <span class="text-xs shrink-0 px-2 py-0.5 rounded-full font-semibold"
                                        style="background: rgba(245, 158, 11, 0.15); color: #b45309;">VIP</span>
                                @endif
                            </div>
                            <p class="text-sm line-clamp-2 mb-4" style="color: var(--gray);">{{ $article->summary }}</p>
                            <div class="flex justify-between text-xs" style="color: var(--gray-light);">
                                <span>{{ $article->published_at?->format('Y-m-d') }}</span>
                                <span>👁 {{ number_format($article->view_count) }} · ❤️ {{ number_format($article->like_count) }}</span>
                            </div>
                        </div>
                    @if ($vipLocked)
                        </div>
                        <x-vip-content-lock />
                    @else
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-10 flex justify-center">
            {{ $articles->onEachSide(1)->links() }}
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        (function () {
            const input = document.getElementById('q');
            const form = document.getElementById('article-filter-form');
            if (!input || !form) return;
            let t = null;
            input.addEventListener('input', function () {
                clearTimeout(t);
                t = setTimeout(function () {
                    form.requestSubmit();
                }, 500);
            });
        })();
    </script>
@endpush
