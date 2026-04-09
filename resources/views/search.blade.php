@extends('layouts.site')

@section('title', '搜索' . ($q !== '' ? '：' . $q : '') . ' — ' . ($ocSite['site_name'] ?? 'OpenClaw 智信'))

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold oc-heading mb-6">搜索</h1>
        <form method="get" action="{{ route('search') }}" class="flex flex-wrap gap-3 mb-10">
            <input
                type="search"
                name="q"
                value="{{ $q }}"
                placeholder="输入关键词"
                class="oc-input flex-1 min-w-[200px]"
                maxlength="200"
                autocomplete="off"
            />
            <button type="submit" class="btn btn-primary text-sm">搜索</button>
        </form>

        @if ($q === '')
            <p class="text-sm oc-muted m-0">输入关键词后搜索文章、项目与用户投稿。</p>
        @else
            <div class="space-y-10">
                <section>
                    <h2 class="text-lg font-bold oc-heading mb-3">文章</h2>
                    @forelse ($articles as $article)
                        <a href="{{ route('articles.show', $article) }}" class="block oc-surface p-4 rounded-xl mb-3 oc-quick-tile" style="text-decoration: none;">
                            <div class="font-semibold oc-heading">{{ $article->title }}</div>
                            @if ($article->summary)
                                <p class="text-sm oc-muted m-0 mt-1 line-clamp-2">{{ $article->summary }}</p>
                            @endif
                        </a>
                    @empty
                        <p class="text-sm oc-muted m-0">无匹配文章。</p>
                    @endforelse
                </section>

                <section>
                    <h2 class="text-lg font-bold oc-heading mb-3">项目</h2>
                    @forelse ($projects as $project)
                        <a href="{{ route('projects.show', $project) }}" class="block oc-surface p-4 rounded-xl mb-3 oc-quick-tile" style="text-decoration: none;">
                            <div class="font-semibold oc-heading">{{ $project->name }}</div>
                            @if ($project->description)
                                <p class="text-sm oc-muted m-0 mt-1 line-clamp-2">{{ $project->description }}</p>
                            @endif
                        </a>
                    @empty
                        <p class="text-sm oc-muted m-0">无匹配项目。</p>
                    @endforelse
                </section>

                <section>
                    <h2 class="text-lg font-bold oc-heading mb-3">用户投稿</h2>
                    @forelse ($userPosts as $post)
                        <a href="{{ route('posts.show', $post) }}" class="block oc-surface p-4 rounded-xl mb-3 oc-quick-tile" style="text-decoration: none;">
                            <div class="font-semibold oc-heading">{{ $post->title }}</div>
                            <p class="text-sm oc-muted m-0 mt-1 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags((string) $post->content), 160) }}</p>
                        </a>
                    @empty
                        <p class="text-sm oc-muted m-0">无匹配投稿。</p>
                    @endforelse
                </section>
            </div>
        @endif
    </div>
@endsection
