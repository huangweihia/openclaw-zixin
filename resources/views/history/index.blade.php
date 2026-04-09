@extends('layouts.site')

@section('title', '浏览历史 — OpenClaw 智信')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold oc-heading mb-2 m-0">浏览历史</h1>
                <p class="text-sm oc-muted m-0">登录后访问文章、项目、案例、工具与社区投稿会自动记录（可在设置中开启隐私模式停止记录）。</p>
            </div>
            @if ($histories->total() > 0)
                <form method="post" action="{{ route('history.clear') }}" onsubmit="return confirm('确定清空全部浏览历史？不可恢复');">
                    @csrf
                    <button type="submit" class="btn text-sm" style="border-color: #b91c1c; color: #b91c1c;">清空全部</button>
                </form>
            @endif
        </div>

        @if (session('success'))
            <div class="oc-flash oc-flash--success mb-4 text-sm" role="status">{{ session('success') }}</div>
        @endif

        <form method="get" action="{{ route('history.index') }}" class="oc-surface p-4 mb-6 flex flex-wrap gap-3 items-end">
            <div class="oc-field mb-0">
                <label class="oc-label text-xs" for="h-type">内容类型</label>
                <select name="type" id="h-type" class="oc-input text-sm" onchange="this.form.submit()">
                    <option value="" @selected($filterType === '')>全部</option>
                    <option value="article" @selected($filterType === 'article')>文章</option>
                    <option value="project" @selected($filterType === 'project')>项目</option>
                    <option value="case" @selected($filterType === 'case')>案例</option>
                    <option value="tool" @selected($filterType === 'tool')>工具</option>
                    <option value="post" @selected($filterType === 'post')>投稿</option>
                </select>
            </div>
            <div class="oc-field mb-0">
                <label class="oc-label text-xs" for="h-date">浏览时间</label>
                <select name="date" id="h-date" class="oc-input text-sm" onchange="this.form.submit()">
                    <option value="" @selected($filterDate === '')>全部</option>
                    <option value="today" @selected($filterDate === 'today')>今天</option>
                    <option value="yesterday" @selected($filterDate === 'yesterday')>昨天</option>
                    <option value="7_days" @selected($filterDate === '7_days')>最近 7 天</option>
                    <option value="30_days" @selected($filterDate === '30_days')>最近 30 天</option>
                </select>
            </div>
        </form>

        <div class="space-y-3">
            @forelse ($histories as $h)
                @php
                    $v = $h->viewable;
                    $title = '内容';
                    $url = '#';
                    $typeLabel = '记录';
                    if ($v instanceof \App\Models\Article) {
                        $title = $v->title;
                        $url = route('articles.show', $v);
                        $typeLabel = '文章';
                    } elseif ($v instanceof \App\Models\Project) {
                        $title = $v->name;
                        $url = route('projects.show', $v);
                        $typeLabel = '项目';
                    } elseif ($v instanceof \App\Models\UserPost) {
                        $title = $v->title;
                        $url = route('posts.show', $v);
                        $typeLabel = '投稿';
                    } elseif ($v instanceof \App\Models\SideHustleCase) {
                        $title = $v->title;
                        $url = route('cases.show', $v);
                        $typeLabel = '案例';
                    } elseif ($v instanceof \App\Models\AiToolMonetization) {
                        $title = $v->tool_name;
                        $url = route('tools.show', $v);
                        $typeLabel = '工具';
                    }
                @endphp
                <div class="oc-surface p-4 flex flex-wrap items-center justify-between gap-3 oc-history-card transition-transform hover:-translate-y-0.5">
                    <div class="min-w-0 flex-1">
                        <span class="text-xs oc-muted">{{ $h->viewed_at->format('Y-m-d H:i') }} · {{ $typeLabel }}</span>
                        @if ($v)
                            <a href="{{ $url }}" class="block font-medium oc-heading mt-1 truncate hover:underline" style="text-decoration: none;">{{ $title }}</a>
                        @else
                            <span class="text-sm oc-muted mt-1 block">内容已删除</span>
                        @endif
                    </div>
                    @if ($v)
                        <form method="post" action="{{ route('history.destroy', $h) }}" class="shrink-0" onsubmit="return confirm('删除这条浏览记录？');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs" style="border: none; background: none; cursor: pointer; color: var(--primary, #0d9488); text-decoration: underline;">删除</button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="oc-surface p-8 text-center text-sm oc-muted">暂无浏览记录</div>
            @endforelse
        </div>

        <div class="mt-6">{{ $histories->links() }}</div>
    </div>
@endsection
