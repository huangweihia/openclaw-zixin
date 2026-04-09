@extends('layouts.site')

@section('title', '项目 — OpenClaw 智信')

@section('content')
    <div class="mb-4">
        <h1 class="text-3xl font-bold mb-2 oc-heading">开源 / 副业项目</h1>
        <p class="text-sm oc-muted">分类、排序与搜索</p>
    </div>

    <div class="oc-surface p-4 md:p-6 mb-5">
        <form method="get" action="{{ route('projects.index') }}" id="project-filter-form" class="space-y-4">
            <div class="flex flex-wrap gap-2 items-center">
                <span class="text-sm font-medium w-full md:w-auto oc-muted">分类</span>
                <a href="{{ route('projects.index', array_filter(['sort' => $currentSort, 'q' => $searchQ ?: null, 'language' => $currentLanguage ?: null])) }}"
                    class="oc-filter-pill {{ $currentCategory === '' ? 'oc-filter-pill--active' : '' }}">全部</a>
                @foreach ($categories as $cat)
                    <a href="{{ route('projects.index', array_filter(['category' => $cat->slug, 'sort' => $currentSort, 'q' => $searchQ ?: null, 'language' => $currentLanguage ?: null])) }}"
                        class="oc-filter-pill {{ $currentCategory === $cat->slug ? 'oc-filter-pill--active' : '' }}">{{ $cat->name }}</a>
                @endforeach
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                <span class="text-sm font-medium w-full md:w-auto oc-muted">排序</span>
                <a href="{{ route('projects.index', array_filter(['category' => $currentCategory ?: null, 'sort' => 'stars', 'q' => $searchQ ?: null, 'language' => $currentLanguage ?: null])) }}"
                    class="oc-filter-pill {{ $currentSort === 'stars' ? 'oc-filter-pill--active' : '' }}">Star</a>
                <a href="{{ route('projects.index', array_filter(['category' => $currentCategory ?: null, 'sort' => 'latest', 'q' => $searchQ ?: null, 'language' => $currentLanguage ?: null])) }}"
                    class="oc-filter-pill {{ $currentSort === 'latest' ? 'oc-filter-pill--active' : '' }}">最新</a>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                <label for="q" class="text-sm font-medium shrink-0 oc-muted">搜索</label>
                <input type="search" name="q" id="q" value="{{ $searchQ }}" placeholder="名称 / 描述 / 仓库"
                    class="oc-input flex-1 max-w-xl" autocomplete="off" />
                @if ($currentCategory !== '')
                    <input type="hidden" name="category" value="{{ $currentCategory }}" />
                @endif
                <input type="hidden" name="sort" value="{{ $currentSort }}" />
                @if ($currentLanguage !== '')
                    <input type="hidden" name="language" value="{{ $currentLanguage }}" />
                @endif
                <button type="submit" class="btn btn-secondary text-sm">搜索</button>
            </div>
        </form>
    </div>

    @if ($projects->isEmpty())
        <div class="oc-surface p-12 text-center oc-muted">暂无项目</div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($projects as $project)
                <a href="{{ route('projects.show', $project) }}" class="oc-article-card">
                    <div class="oc-card-thumb flex items-center justify-center text-2xl" style="background: var(--light);">📦</div>
                    <div class="p-5">
                        <h2 class="text-lg font-bold line-clamp-2 oc-heading mb-2">{{ $project->name }}</h2>
                        <p class="text-sm line-clamp-2 oc-muted mb-4">{{ \Illuminate\Support\Str::limit(strip_tags($project->description ?? ''), 120) }}</p>
                        <div class="flex flex-wrap gap-2 text-xs oc-muted">
                            @if ($project->language)
                                <span class="px-2 py-0.5 rounded" style="background: var(--light);">{{ $project->language }}</span>
                            @endif
                            <span>⭐ {{ number_format($project->stars) }}</span>
                            <span>⑂ {{ number_format($project->forks) }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-10 flex justify-center">
            {{ $projects->onEachSide(1)->links() }}
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        (function () {
            const input = document.getElementById('q');
            const form = document.getElementById('project-filter-form');
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
