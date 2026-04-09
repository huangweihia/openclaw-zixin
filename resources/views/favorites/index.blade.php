@extends('layouts.site')

@section('title', '我的收藏 — OpenClaw 智信')

@section('content')
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold oc-heading">我的收藏</h1>
            <p class="text-sm oc-muted mt-1">文章 / 项目 / 案例 / 投稿</p>
        </div>
    </div>

    <div class="oc-surface p-4 mb-8">
        <div class="flex flex-wrap gap-2">
            <span class="text-sm font-medium oc-muted self-center">类型</span>
            <a href="{{ route('favorites.index') }}" class="oc-filter-pill {{ ($currentType ?? '') === '' ? 'oc-filter-pill--active' : '' }}">全部</a>
            <a href="{{ route('favorites.index', ['type' => 'article']) }}" class="oc-filter-pill {{ ($currentType ?? '') === 'article' ? 'oc-filter-pill--active' : '' }}">文章</a>
            <a href="{{ route('favorites.index', ['type' => 'project']) }}" class="oc-filter-pill {{ ($currentType ?? '') === 'project' ? 'oc-filter-pill--active' : '' }}">项目</a>
            <a href="{{ route('favorites.index', ['type' => 'case']) }}" class="oc-filter-pill {{ ($currentType ?? '') === 'case' ? 'oc-filter-pill--active' : '' }}">案例</a>
            <a href="{{ route('favorites.index', ['type' => 'post']) }}" class="oc-filter-pill {{ ($currentType ?? '') === 'post' ? 'oc-filter-pill--active' : '' }}">投稿</a>
        </div>
    </div>

    <form method="post" action="{{ route('favorites.bulk-delete') }}" id="favorites-bulk-form" class="mb-4 flex flex-wrap items-center gap-3"
        onsubmit="return confirm('确定删除所选收藏？');">
        @csrf
        <button type="submit" class="btn btn-secondary text-sm" {{ $actions->isEmpty() ? 'disabled' : '' }}>批量删除所选</button>
    </form>

    @if ($actions->isEmpty())
        <div class="oc-surface p-12 text-center oc-muted">暂无收藏</div>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($actions as $action)
                @php
                    $item = $action->actionable;
                    $typeLabel = '内容';
                    $title = '已删除';
                    $href = '#';
                    if ($item instanceof \App\Models\Article) {
                        $typeLabel = '文章';
                        $title = $item->title;
                        $href = route('articles.show', $item);
                    } elseif ($item instanceof \App\Models\Project) {
                        $typeLabel = '项目';
                        $title = $item->name;
                        $href = route('projects.show', $item);
                    } elseif ($item instanceof \App\Models\SideHustleCase) {
                        $typeLabel = '案例';
                        $title = $item->title;
                        $href = route('cases.show', $item);
                    } elseif ($item instanceof \App\Models\UserPost) {
                        $typeLabel = '投稿';
                        $title = $item->title;
                        $href = route('posts.show', $item);
                    }
                @endphp
                <div class="oc-article-card relative group">
                    <div class="absolute top-3 right-3 z-10 flex items-center gap-2">
                        <input type="checkbox" form="favorites-bulk-form" name="ids[]" value="{{ $action->id }}" class="oc-checkbox" aria-label="选择" />
                        <form method="post" action="{{ route('favorites.destroy', $action) }}" class="inline" onsubmit="return confirm('移除此收藏？');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="oc-comment__btn text-xs opacity-0 group-hover:opacity-100 transition-opacity">移除</button>
                        </form>
                    </div>
                    <a href="{{ $href }}" class="block p-5 pt-10">
                        <span class="text-xs font-semibold oc-muted">{{ $typeLabel }}</span>
                        <h2 class="text-lg font-bold line-clamp-2 oc-heading mt-1">{{ $title }}</h2>
                        <span class="text-xs oc-muted mt-2 block">{{ $action->created_at->format('Y-m-d H:i') }}</span>
                    </a>
                </div>
            @endforeach
        </div>
        <div class="mt-10 flex justify-center">
            {{ $actions->onEachSide(1)->links() }}
        </div>
    @endif
@endsection
